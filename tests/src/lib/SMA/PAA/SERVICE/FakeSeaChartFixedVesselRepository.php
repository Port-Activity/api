<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\SeaChartFixedVesselModel;

class FakeSeaChartFixedVesselRepository
{
    public $returnFixedVessel = true;
    public $lastSavedFixedVessel;
    public $returnVesselTypeWithFixedVessel = true;

    public function getFixedVessels(): array
    {
        $fixedVessels = array();

        if ($this->returnFixedVessel) {
            $fakeVessel = new SeaChartFixedVesselModel();
            $fakeVessel->id = 1;
            $fakeVessel->imo = 222222;
            $fakeVessel->mmsi = 222222;
            $fakeVessel->vessel_name = 'Fake';
            $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
            $fixedVessels[] = $fakeVessel;
        }

        return $fixedVessels;
    }

    public function getFixedVesselByImo(int $imo): ?SeaChartFixedVesselModel
    {
        if ($imo != 888888 && $imo != 111111 && $imo != 999999) {
            $fakeVessel = new SeaChartFixedVesselModel();
            $fakeVessel->id = 1;
            $fakeVessel->imo = 222222;
            $fakeVessel->mmsi = 222222;
            $fakeVessel->vessel_name = 'Fake';
            $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
            return $fakeVessel;
        }
        return null;
    }

    public function getFixedVesselByMmsi(int $mmsi): ?SeaChartFixedVesselModel
    {
        if ($mmsi != 888888 && $mmsi != 111111) {
            $fakeVessel = new SeaChartFixedVesselModel();
            $fakeVessel->id = 1;
            $fakeVessel->imo = 222222;
            $fakeVessel->mmsi = 222222;
            $fakeVessel->vessel_name = 'Fake';
            $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
            return $fakeVessel;
        }
        return null;
    }

    public function getFixedVesselByImoAndMmsi(int $imo, int $mmsi): ?SeaChartFixedVesselModel
    {
        if ($imo != 888888 && $mmsi != 888888) {
            $fakeVessel = new SeaChartFixedVesselModel();
            $fakeVessel->id = 1;
            $fakeVessel->imo = 222222;
            $fakeVessel->mmsi = 222222;
            $fakeVessel->vessel_name = 'Fake';
            $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
            return $fakeVessel;
        }
        return null;
    }

    public function save(SeaChartFixedVesselModel $model, bool $skipCrudLog = false): int
    {
        $this->lastSavedFixedVessel = $model;
        return ($model->mmsi === 999999) ? 0 : 1;
    }

    public function delete(array $ids, bool $skipCrudLog = false)
    {
        return empty($ids) || $ids[array_key_first($ids)] === 999999
            ? null : [];
    }

    public function listPaginated(
        array $query,
        int $start,
        int $count,
        string $orderBy = "id"
    ): ?array {
        $data = ["test-key" => "test-data"];
        $pagination = [
            "start" => $start,
            "limit" => $count,
            "total" => 100
        ];

        return [
            "data" => $data,
            "pagination" => [$pagination]
        ];
    }

    public function buildJoinSelect()
    {
    }

    public function list(array $query, int $start, int $count): ?array
    {
        $result = array();
        for ($index = 0; $index < $count; $index++) {
            $result[] = array("test-key" => "test-data");
        }
        return $result;
    }

    public function first(array $query, string $orderBy = "id"): ?SeaChartFixedVesselModel
    {
        if (isset($this->lastSavedFixedVessel)) {
            return $this->lastSavedFixedVessel;
        }

        $fakeVessel = new SeaChartFixedVesselModel();
        $fakeVessel->id = 1;
        $fakeVessel->imo = 222222;
        $fakeVessel->mmsi = 222222;
        $fakeVessel->vessel_name = 'Fake';
        $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
        return $fakeVessel;
    }

    public function get(int $id): ?SeaChartFixedVesselModel
    {
        if (isset($this->lastSavedFixedVessel) && $this->lastSavedFixedVessel->id === $id) {
            return $this->lastSavedFixedVessel;
        } elseif ($id === 99999999) {
            return null;
        } else {
            $fakeVessel = new SeaChartFixedVesselModel();
            $fakeVessel->id = 1;
            $fakeVessel->imo = 222222;
            $fakeVessel->mmsi = 222222;
            $fakeVessel->vessel_name = 'Fake';
            $fakeVessel->vessel_type = $this->returnVesselTypeWithFixedVessel ? 10 : null;
            return $fakeVessel;
        }
    }
}
