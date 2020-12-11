<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\PortCallPayloadKeyWeightModel;

class PortCallPayloadKeyWeightRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function queryModel(
        int $portCallId,
        string $payloadKey
    ): ?PortCallPayloadKeyWeightModel {
        $query = [];
        $query["port_call_id"] = $portCallId;
        $query["payload_key"] = $payloadKey;

        return $this->first($query);
    }

    public function getWeight(
        int $portCallId,
        string $payloadKey
    ): int {
        $model = $this->queryModel($portCallId, $payloadKey);

        if ($model === null) {
            return -1;
        }

        return $model->weight;
    }

    public function setWeight(
        int $portCallId,
        string $payloadKey,
        int $weight
    ) {
        if ($weight === 0) {
            return;
        }

        $model = $this->queryModel($portCallId, $payloadKey);
        if ($model === null) {
            $model = new PortCallPayloadKeyWeightModel();
        }

        $model->set($portCallId, $payloadKey, $weight);
        $this->save($model);
    }
}
