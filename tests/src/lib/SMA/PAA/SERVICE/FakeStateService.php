<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\SERVICE\IStateService;

class FakeStateService implements IStateService
{
    public $latestPortCallsData;
    public $latestSeaChartVesselsAndMarkers;
    public $deletedKeys = [];

    public function get(string $key)
    {
        if ($key === StateService::LATEST_PORT_CALL_DETAILS) {
            return $this->latestPortCallsData;
        } elseif ($key === StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS) {
            return $this->latestSeaChartVesselsAndMarkers;
        }

        return null;
    }
    public function getSet(string $key, callable $callback)
    {
        if ($key === StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS
            && !empty($this->latestSeaChartVesselsAndMarkers)) {
            return $this->latestSeaChartVesselsAndMarkers;
        }
        return call_user_func($callback);
    }
    public function delete(string $key)
    {
        $this->deletedKeys[] = $key;
    }
    public function set(string $key, $data)
    {
    }
    public function rebuildInitialSharedData()
    {
    }
}
