<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\TimestampApiKeyWeightRepository;
use SMA\PAA\ORM\TimestampApiKeyWeightModel;
use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\ApiKeyRepository;
use SMA\PAA\ORM\ApiKeyModel;
use SMA\PAA\ORM\TimestampTimeTypeRepository;
use SMA\PAA\ORM\TimestampStateRepository;
use SMA\PAA\SERVICE\ApiKeyService;
use SMA\PAA\SERVICE\PortCallTemplateService;

class TimestampApiKeyWeightService implements ITimestampApiKeyWeightService
{
    public function checkApiKeyPermission(TimestampModel $timestampModel): bool
    {
        $apiKeyService = new ApiKeyService();
        $apiKeyId = $apiKeyService->getApiKeyId();

        // Null API key means that timestamp is added manually
        // Manual timestamps are always allowed
        if ($apiKeyId === null) {
            return true;
        }

        $repository = new TimestampApiKeyWeightRepository();

        if ($repository->queryModel(
            $timestampModel->time_type_id,
            $timestampModel->state_id,
            $apiKeyId
        ) !== null) {
            return true;
        }

        return false;
    }

    public function list(): array
    {
        $res = [];
        $res["api_keys"] = [];
        $res["timestamps"] = [];

        $apiKeyMap = [];

        $repository = new TimestampApiKeyWeightRepository();

        $apiKeyRepository = new ApiKeyRepository();
        $apiKeys = $apiKeyRepository->list([], 0, 1000, "name");
  
        foreach ($apiKeys as $apiKey) {
            $apiKeyMap[$apiKey->id] = $apiKey->name;

            $innerRes = [];
            $innerRes["id"] = $apiKey->id;
            $innerRes["name"] = $apiKey->name;
            $innerRes["is_active"] = $apiKey->is_active;

            $res["api_keys"][] = $innerRes;
        }

        $portCallTemplateService = new PortCallTemplateService();
        $rawTemplateEntries = $portCallTemplateService->get(getenv("NAMESPACE"));
        $templateEntries = [];
        foreach ($rawTemplateEntries as $rawTemplateEntry) {
            $duplicate = false;

            foreach ($templateEntries as $templateEntry) {
                if ($templateEntry["time_type"] === $rawTemplateEntry["time_type"] &&
                   $templateEntry["state"] === $rawTemplateEntry["state"]) {
                       $duplicate = true;
                    break;
                }
            }

            if (!$duplicate) {
                $innerRes = [];
                $innerRes["time_type"] = $rawTemplateEntry["time_type"];
                $innerRes["state"] = $rawTemplateEntry["state"];
                $templateEntries[] = $innerRes;
            }
        }

        $timestampTimeTypeRepository = new TimestampTimeTypeRepository();
        $timestampTimeTypes = $timestampTimeTypeRepository->getTimeTypeMappings();
        $timestampStateRepository = new TimestampStateRepository();
        $timestampStates = $timestampStateRepository->getStateMappings();

        foreach ($templateEntries as $templateEntry) {
            if (isset($timestampTimeTypes[$templateEntry["time_type"]]) &&
               isset($timestampStates[$templateEntry["state"]])) {
                $innerRes = [];
                $innerRes["time_type"] = $templateEntry["time_type"];
                $innerRes["state"] = $templateEntry["state"];

                $innerRes["api_keys"] = [];
                $query = [];
                $query["timestamp_time_type_id"] = $timestampTimeTypes[$templateEntry["time_type"]];
                $query["timestamp_state_id"] = $timestampStates[$templateEntry["state"]];
                $apiKeys = $repository->list($query, 0, 1000, "weight DESC");

                foreach ($apiKeys as $apiKey) {
                    $innerSource = [];
                    $innerSource["api_key_id"] = $apiKey->api_key_id;

                    if (isset($apiKeyMap[$apiKey->api_key_id])) {
                        $innerSource["api_key_name"] = $apiKeyMap[$apiKey->api_key_id];
                    } else {
                        $innerSource["api_key_name"] = "";
                    }
                    
                    $innerSource["weight"] = $apiKey->weight;

                    $innerRes["api_keys"][] = $innerSource;
                }

                $res["timestamps"][] = $innerRes;
            }
        }

        return $res;
    }

    public function modify(
        string $timestamp_time_type,
        string $timestamp_state,
        array $api_key_ids
    ): array {
        if (count(array_unique($api_key_ids)) < count($api_key_ids)) {
            throw new InvalidParameterException("Duplicate API key ID values not allowed");
        }


        $portCallTemplateService = new PortCallTemplateService();
        $templateEntries = $portCallTemplateService->get(getenv("NAMESPACE"));
        $timestampTimeTypeRepository = new TimestampTimeTypeRepository();
        $timestampTimeTypes = $timestampTimeTypeRepository->getTimeTypeMappings();
        $timestampStateRepository = new TimestampStateRepository();
        $timestampStates = $timestampStateRepository->getStateMappings();

        $typeIds = [];
        $stateIds = [];
        foreach ($templateEntries as $templateEntry) {
            if (isset($timestampTimeTypes[$templateEntry["time_type"]]) &&
               isset($timestampStates[$templateEntry["state"]])) {
                $typeIds[$templateEntry["time_type"]] = $timestampTimeTypes[$templateEntry["time_type"]];
                $stateIds[$templateEntry["state"]] = $timestampStates[$templateEntry["state"]];
            }
        }

        if (!isset($typeIds[$timestamp_time_type])) {
            throw new InvalidParameterException("Invalid timestamp time type: " . $timestamp_time_type);
        }

        if (!isset($stateIds[$timestamp_state])) {
            throw new InvalidParameterException("Invalid timestamp state: " . $timestamp_state);
        }

        $typeId = $typeIds[$timestamp_time_type];
        $stateId = $stateIds[$timestamp_state];

        $apiKeyRepository = new ApiKeyRepository();
        $apiKeyResults = $apiKeyRepository->list([], 0, 1000);
        $apiKeyIds = [];
        foreach ($apiKeyResults as $apiKeyResult) {
            $apiKeyIds[] = $apiKeyResult->id;
        }

        $apiKeyCount = count($api_key_ids);
        $apiKeyWeightMap = [];
        $ct = 0;
        foreach ($api_key_ids as $apiKeyId) {
            if (!in_array($apiKeyId, $apiKeyIds)) {
                throw new InvalidParameterException("Invalid api key ID: " . $apiKeyId);
            }

            $apiKeyWeightMap[$apiKeyId] = $apiKeyCount - $ct;
            $ct += 1;
        }

        $repository = new TimestampApiKeyWeightRepository();
        $query = [];
        $query["timestamp_time_type_id"] = $typeId;
        $query["timestamp_state_id"] = $stateId;
        $models = $repository->list($query, 0, 1000);

        // If given API keys empty then just delete existing entries
        if (empty($api_key_ids)) {
            foreach ($models as $model) {
                $repository->delete([$model->id]);
            }

            return ["result" => "OK"];
        }

        // Avoid unnecessary DB changes
        $change = false;

        // If API key count differs from existing, then we have change
        if (count($models) !== count($api_key_ids)) {
            $change = true;
        }

        // If API key does not exist in current models we have change
        // If API key weight differs from current models we have change
        if (!$change) {
            foreach ($models as $model) {
                if (!isset($apiKeyWeightMap[$model->api_key_id])) {
                    $change = true;
                    break;
                } else {
                    if ($model->weight !== $apiKeyWeightMap[$model->api_key_id]) {
                        $change = true;
                        break;
                    }
                }
            }
        }

        // No change, do nothing
        if (!$change) {
            return ["result" => "OK"];
        }

        // We have change, delete old models and store new
        foreach ($models as $model) {
            $repository->delete([$model->id]);
        }

        foreach ($apiKeyWeightMap as $k => $v) {
            $model = new TimestampApiKeyWeightModel();
            $model->set($typeId, $stateId, $k, $v);
            $repository->save($model);
        }

        return ["result" => "OK"];
    }
}
