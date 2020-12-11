<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\PayloadKeyApiKeyWeightModel;

class PayloadKeyApiKeyWeightRepository extends OrmRepository
{
    const MAX_PAYLOAD_KEY_WEIGHT = 999;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function queryModel(
        string $payloadKey,
        int $apiKeyId
    ): ?PayloadKeyApiKeyWeightModel {
        $query = [];
        $query["payload_key"] = $payloadKey;
        $query["api_key_id"] = $apiKeyId;

        return $this->first($query);
    }

    public function getApiKeyWeight(
        string $payloadKey,
        int $apiKeyId
    ): int {
        $model = $this->queryModel($payloadKey, $apiKeyId);

        if ($model === null) {
            return 0;
        }

        return $model->weight;
    }
}
