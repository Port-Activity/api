<?php
namespace SMA\PAA\ORM;

use SMA\PAA\SERVICE\StateService;

class LogisticsTimestampRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function isDuplicate(LogisticsTimestampModel $needle): bool
    {
        return $this->first([
            "time" => $needle->time,
            "checkpoint" => $needle->checkpoint,
            "direction" => $needle->direction,
            "payload" => $needle->payload,
        ]) !== null;
    }

    public function getAllLogisticsTimestamps(int $limit): array
    {
        $result = $this->getMultipleWithQuery("SELECT * FROM {$this->table} "
        . "ORDER BY time DESC "
        . "LIMIT '{$limit}'");
        return $result;
    }

    private function pretty(array $rawResults): array
    {
        $res = [];
        foreach ($rawResults as $rawResult) {
            $prettyResult = new LogisticsTimestampPrettyModel();
            $prettyResult->setFromLogisticsTimestamp($rawResult);
            $res[] = $prettyResult;
        }
        return $res;
    }

    public function getAllLogisticsTimestampsPretty(int $limit): array
    {
        return $this->pretty($this->getAllLogisticsTimestamps($limit));
    }

    public function getLogisticsTimestampsFiltered(int $limit, array $licensePlates, array $checkpoints = []): array
    {
        $in = implode(",", array_fill(0, sizeof($licensePlates), "?"));
        $inCheckpoints = implode(",", array_fill(0, sizeof($checkpoints), "?"));
        // Note: jsonb_array_elements(l.payload->'rear_license_plates') fails if empty array
        // Thats why we need do trick replace(l.payload->>'rear_license_plates', '[]', '[1]')::jsonb
        // not to miss any correct rows.
        $sql =
            "SELECT * FROM {$this->table} l, "
            . "jsonb_array_elements(replace(l.payload->>'front_license_plates', '[]', '[1]')::jsonb) f, "
            . "jsonb_array_elements(replace(l.payload->>'rear_license_plates', '[]', '[1]')::jsonb) r "
            . "WHERE "
            . ( $checkpoints ? "checkpoint in (" . $inCheckpoints . ") AND " : "")
            . "("
              . "f->>'number' in (" . $in . ") "
              . "OR r->>'number' in (". $in .")"
            . ") "
            . "ORDER BY time DESC "
            . "LIMIT " . $limit
        ;
        return $this->getMultipleWithQuery($sql, ...$checkpoints, ...$licensePlates, ...$licensePlates);
    }

    public function getLogisticsTimestampsFilteredPretty(int $limit, array $licensePlates, array $checkpoints): array
    {
        return $this->pretty($this->getLogisticsTimestampsFiltered($limit, $licensePlates, $checkpoints));
    }

    public function getLogisticsTimestamps(string $licensePlate, $limit = 100): array
    {
        return $this->getLogisticsTimestampsFiltered($limit, [$licensePlate]);
    }

    public function getLogisticsTimestampsPretty(string $licensePlate, int $limit = 100): array
    {
        return $this->pretty($this->getLogisticsTimestamps($licensePlate, $limit));
    }
    public function deleteRelatedStates()
    {
        $service = new StateService();
        $service->triggerLogistics();
    }
    public function save(OrmModel $model, bool $skipCrud = false)
    {
        $data = parent::save($model, $skipCrud);
        $this->deleteRelatedStates();
        return $data;
    }
}
