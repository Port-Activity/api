<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\TimestampPrettyModel;
use SMA\PAA\ORM\TimestampApiKeyWeightRepository;
use SMA\PAA\SERVICE\StateService;
use SMA\PAA\SERVICE\ApiKeyService;

class TimestampRepository extends OrmRepository
{
    const MAX_TIMESTAMP_WEIGHT = 999;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    public function deleteRelatedStates()
    {
        $service = new StateService();
        $service->triggerPortCalls();
    }
    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        // Save timestamp weight for new models
        if (!$model->id) {
            $apiKeyService = new ApiKeyService();
            $apiKeyId = $apiKeyService->getApiKeyId();

            $weightModel = null;
            if ($apiKeyId !== null) {
                $weightRepository = new TimestampApiKeyWeightRepository();
                $weightModel = $weightRepository->queryModel($model->time_type_id, $model->state_id, $apiKeyId);
            }

            if ($weightModel !== null) {
                $model->weight = $weightModel->weight;
            } else {
                $model->weight = TimestampRepository::MAX_TIMESTAMP_WEIGHT;
            }
        }

        $data = parent::save($model, $skipCrudLog);
        return $data;
    }

    public function isDuplicate(TimestampModel $needle): bool
    {
        return $this->first([
            "imo" => $needle->imo,
            "vessel_name" => $needle->vessel_name,
            "time_type_id" => $needle->time_type_id,
            "state_id" => $needle->state_id,
            "time" => $needle->time,
            "payload" => $needle->payload
        ]) !== null;
    }

    public function getAllTimestamps(int $limit): array
    {
        $result = $this->getMultipleWithQuery("SELECT * FROM {$this->table} "
        . "ORDER BY time DESC "
        . "LIMIT '{$limit}'");
        return $result;
    }

    public function getAllTimestampsPretty(int $limit): array
    {
        $rawResults = $this->getAllTimestamps($limit);

        $res = [];
        foreach ($rawResults as $rawResult) {
            $prettyResult = new TimestampPrettyModel();
            $prettyResult->setFromTimestamp($rawResult);
            $res[] = $prettyResult;
        }

        return $res;
    }
    public function getTimestamps(int $imo): array
    {
        return $this->list(["imo" => $imo], 0, 1000, "time DESC");
    }
    public function getTimestampsByPortCallId(int $portCallId, int $offset = 0, int $limit = 1000): array
    {
        return $this->list(["port_call_id" => $portCallId], $offset, $limit, "id DESC");
    }
    public function byNoPortCallId(int $limit, int $offset = 0): array
    {
        $result = $this->list(
            ["port_call_id" => null, "is_trash" => "f"],
            $offset,
            $limit,
            "created_at"
        );
        return $this->pretty($result);
    }

    public function byNoPortCallIdAndImo(int $limit, int $imo): array
    {
        $result = $this->list(
            ["port_call_id" => null, "imo" => $imo],
            0,
            $limit
        );
        return $this->pretty($result);
    }

    public function pretty(array $rawResults)
    {
        $res = [];
        foreach ($rawResults as $rawResult) {
            $prettyResult = new TimestampPrettyModel();
            $prettyResult->setFromTimestamp($rawResult);
            $res[] = $prettyResult;
        }

        return $res;
    }
    public function getTimestampsPretty(int $imo): array
    {
        return $this->pretty($this->getTimestamps($imo));
    }
    public function getTimestampsForPortCallPretty(int $portCallId)
    {
        return $this->pretty($this->getTimestampsByPortCallId($portCallId));
    }

    public function exportTimestamps(
        int $id = null,
        int $imo = null,
        int $portCallId = null,
        string $startDateTime = null,
        string $endDateTime = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $query = [];
        if (isset($id)) {
            $query["id"] = $id;
        }

        if (isset($imo)) {
            $query["imo"] = $imo;
        }

        if (isset($portCallId)) {
            $query["port_call_id"] = $portCallId;
        }

        if (isset($startDateTime)) {
            $innerQuery["gte"] = $startDateTime;
            $query["time"] = $innerQuery;
        }

        if (isset($endDateTime)) {
            $innerQuery["lte"] = $endDateTime;
            $query["time"] = $innerQuery;
        }

        $offset = isset($offset) ? $offset : 0;
        $limit = isset($limit) ? $limit : 0;
        $orderBy = "time";

        if (isset($sort)) {
            $orderBy = $sort;
        }

        $rawResults = $this->listPaginated($query, $offset, $limit, $orderBy);

        $prettyData = [];
        foreach ($rawResults["data"] as $rawResult) {
            $prettyResult = new TimestampPrettyModel();
            $prettyResult->setFromTimestamp($rawResult);
            $prettyData[] = $prettyResult;
        }

        $res["data"] = $prettyData;
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }
    public function countOfTimestampWithOutPortCallId()
    {
        return $this->count(["port_call_id" => null, "is_trash" => "f"]);
    }
    public function markAsTrash(array $ids)
    {
        foreach ($ids as $id) {
            if (!is_int($id)) {
                throw new \Exception("Invalid id: " . $id);
            }
        }

        $idsString = join(",", $ids);
        return $this->update("UPDATE $this->table SET is_trash=? WHERE id IN ($idsString)", "t");
    }
    public function untrashByImo($imo)
    {
        return $this->update("UPDATE $this->table SET is_trash=? WHERE imo=?", "f", $imo);
    }
    public function nullPortCallsByImo($imo)
    {
        return $this->update("UPDATE $this->table SET port_call_id=? WHERE imo=?", null, $imo);
    }
    public function nullPortCallsByIds(array $ids)
    {
        if (!empty($ids)) {
            $holders = implode(",", array_fill(0, count($ids), "?"));
            return $this->update(
                "UPDATE $this->table SET port_call_id=? WHERE id IN ($holders)",
                null,
                ...array_values($ids)
            );
        }

        return 0;
    }
}
