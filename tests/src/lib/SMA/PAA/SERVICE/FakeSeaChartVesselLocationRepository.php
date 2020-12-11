<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\SeaChartVesselLocationModel;

class FakeSeaChartVesselLocationRepository
{
    public $lastSavedModel;

    public function getLocationByImo(int $imo): ?SeaChartVesselLocationModel
    {
        return $this->constructFakeLocation($imo, $imo);
    }
    public function getLocationByMmsi(int $mmsi): ?SeaChartVesselLocationModel
    {
        return $this->constructFakeLocation($mmsi, $mmsi);
    }
    private function constructFakeLocation(int $imo = null, int $mmsi = null): SeaChartVesselLocationModel
    {
        $fakeLocation = new SeaChartVesselLocationModel();
        $fakeLocation->imo = $imo;
        $fakeLocation->mmsi = $mmsi;
        $fakeLocation->vessel_name = "Fake";
        $fakeLocation->latitude = 55.973345;
        $fakeLocation->longitude = -24.534348;
        $fakeLocation->heading_degrees = 180.0;
        $fakeLocation->speed_knots = 16.4;
        $fakeLocation->location_timestamp = "2020-11-02T12:03:05+00:00";
        $fakeLocation->id = $imo ? $imo : $mmsi ? $mmsi : 99999;
        $fakeLocation->course_over_ground_degrees = 140.0;
        return $fakeLocation;
    }
    public function save(SeaChartVesselLocationModel $model, bool $skipCrudLog = false)
    {
        $this->lastSavedModel = $model;
        return 1;
    }
    public function getLocationsByImos(array $imos): array
    {
        $result = array();
        foreach ($imos as $imo) {
            $result[] = $this->constructFakeLocation($imo, $imo);
        }
        return $result;
    }
}
