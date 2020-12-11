<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\ApiKeyRepository;
use SMA\PAA\ORM\PayloadKeyApiKeyWeightRepository;
use SMA\PAA\ORM\PayloadKeyApiKeyWeightModel;
use SMA\PAA\SERVICE\ApiKeyService;

class PayloadKeyApiKeyWeightService implements IPayloadKeyApiKeyWeightService
{
    public function list(): array
    {
        $res = [];
        $res["api_keys"] = [];
        $res["payload_keys"] = [];

        $apiKeyMap = [];

        $repository = new PayloadKeyApiKeyWeightRepository();

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

        $models = $repository->list([], 0, 1000);
        $payloadKeys = [];
        foreach ($models as $model) {
            $payloadKeys[] = $model->payload_key;
        }
        $payloadKeys = array_unique($payloadKeys);

        foreach ($payloadKeys as $payloadKey) {
            $innerRes = [];
            $innerRes["key"] = $payloadKey;
            $innerRes["api_keys"] = [];

            $query = [];
            $query["payload_key"] = $payloadKey;
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

            $res["payload_keys"][] = $innerRes;
        }

        return $res;
    }

    public function modify(
        string $payload_key,
        array $api_key_ids
    ): array {
        if (count(array_unique($api_key_ids)) < count($api_key_ids)) {
            throw new InvalidParameterException("Duplicate API key ID values not allowed");
        }

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

        $repository = new PayloadKeyApiKeyWeightRepository();
        $query = [];
        $query["payload_key"] = $payload_key;
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
            $model = new PayloadKeyApiKeyWeightModel();
            $model->set($payload_key, $k, $v);
            $repository->save($model);
        }

        return ["result" => "OK"];
    }
}
