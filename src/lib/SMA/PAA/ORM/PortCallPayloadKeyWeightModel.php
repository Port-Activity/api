<?php
namespace SMA\PAA\ORM;

class PortCallPayloadKeyWeightModel extends OrmModel
{
    public $port_call_id;
    public $payload_key;
    public $weight;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $portCallId,
        string $payloadKey,
        int $weight
    ) {
        $this->port_call_id = $portCallId;
        $this->payload_key = $payloadKey;
        $this->weight = $weight;
    }
}
