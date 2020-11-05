<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\TimestampApiKeyWeightModel;

class TimestampApiKeyWeightRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function queryModel(
        int $timeTypeId,
        int $stateId,
        int $apiKeyId
    ): ?TimestampApiKeyWeightModel {
        $query = [];
        $query["timestamp_time_type_id"] = $timeTypeId;
        $query["timestamp_state_id"] = $stateId;
        $query["api_key_id"] = $apiKeyId;

        return $this->first($query);
    }
}
