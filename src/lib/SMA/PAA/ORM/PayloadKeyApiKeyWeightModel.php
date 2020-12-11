<?php
namespace SMA\PAA\ORM;

class PayloadKeyApiKeyWeightModel extends OrmModel
{
    public $payload_key;
    public $api_key_id;
    public $weight;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $payloadKey,
        int $apiKeyId,
        int $weight
    ) {
        $this->payload_key = $payloadKey;
        $this->api_key_id = $apiKeyId;
        $this->weight = $weight;
    }
}
