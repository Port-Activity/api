<?php
namespace SMA\PAA\ORM;

class TimestampApiKeyWeightModel extends OrmModel
{
    public $timestamp_time_type_id;
    public $timestamp_state_id;
    public $api_key_id;
    public $weight;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $timestampTimeTypeId,
        int $timestampStateId,
        int $apiKeyId,
        int $weight
    ) {
        $this->timestamp_time_type_id = $timestampTimeTypeId;
        $this->timestamp_state_id = $timestampStateId;
        $this->api_key_id = $apiKeyId;
        $this->weight = $weight;
    }
}
