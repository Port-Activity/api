<?php
namespace SMA\PAA\SERVICE;

class PortCallFactory
{
    public function __construct(IApiKeyService $apiKeyService = null)
    {
        if (!$apiKeyService) {
            $apiKeyService = new ApiKeyService();
        }
        $this->apiKeyService = $apiKeyService;
    }
    private function toModels(array $timestamps)
    {
        return array_map(function ($timestamp) {
            return new PortCallHelperModel($timestamp);
        }, $timestamps);
    }
    private function toArrays(array $models)
    {
        return array_map(function (PortCallHelperModel $model) {
            $data = $model->getData();
            unset($data["group"]);
            return $data;
        }, $models);
    }
    private function filter(string $group, array $timestampsModels)
    {
        $data = array_filter($timestampsModels, function (PortCallHelperModel $model) use ($group) {
            return $model->group() === $group;
        });
        $out = [];
        foreach ($data as $d) {
            $out[] = $d;
        }
        return $out;
    }
    private function findSource(int $id)
    {
        if (!isset($GLOBALS[__CLASS__][$id])) {
            $service = $this->apiKeyService;
            $GLOBALS[__CLASS__][$id] = $service->userOrApiKeyName($id);
        }
        return $GLOBALS[__CLASS__][$id];
    }
    public function timestampsToPortCall(array $templateTimestamps, array $timestamps)
    {
        $timestamps = json_decode(json_encode($timestamps), true);
        $models = $this->toModels($timestamps);
        $templateModels = $this->toModels($templateTimestamps);
        $hash = [];
        $ensuredTimestamps = [];
        foreach ($models as $model) {
            $hash[$model->buildKey()] = $model;
        }

        foreach ($templateModels as $templateModel) {
            $key = $templateModel->buildKey();
            if (array_key_exists($key, $hash)) {
                $model = $hash[$key];
                $model->setGroup($templateModel->group()); // group is not in db, only in template
                $model->setSource($this->findSource($model->createdBy()));
                $ensuredTimestamps[$key] = $model;
            } else {
                $ensuredTimestamps[$key] = $templateModel;
            }
        };
        //TODO: this could be totally dynamic with groups - for now keep output for current version consistent
        $seaArriving = $this->filter("seaArriving", $ensuredTimestamps);
        $atBerth = $this->filter("atBerth", $ensuredTimestamps);
        $seaDeparting = $this->filter("seaDeparting", $ensuredTimestamps);

        return [
            "id" => 1,
            "events" => [
                [
                    "id" => 1,
                    "location" => "sea",
                    "timestamps" => $this->toArrays($seaArriving)
                ],
                [
                    "id" => 2,
                    "location" => "port",
                    "timestamps" => $this->toArrays($atBerth)
                ],
                [
                    "id" => 3,
                    "location" => "sea",
                    "timestamps" => $this->toArrays($seaDeparting)
                ]
            ]
        ];
    }
}
