<?php
namespace SMA\PAA\ORM;

class SeaChartFixedVesselModel extends OrmModel
{
    public $id;
    public $imo;
    public $mmsi;
    public $vessel_type;
    public $vessel_name;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $id,
        int $imo,
        int $mmsi,
        int $vesselType,
        string $vesselName
    ) {
        $this->id = $id;
        $this->imo = $imo;
        $this->mmsi = $mmsi;
        $this->vessel_type = $vesselType;
        $this->vessel_name = $vesselName;
    }
}
