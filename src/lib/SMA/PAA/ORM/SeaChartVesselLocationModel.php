<?php
namespace SMA\PAA\ORM;

class SeaChartVesselLocationModel extends OrmModel
{
    public $imo;
    public $mmsi;
    public $vessel_name;
    public $latitude;
    public $longitude;
    public $heading_degrees;
    public $speed_knots;
    public $location_timestamp;
    public $course_over_ground_degrees;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $imo,
        int $mmsi,
        string $vesselName,
        float $latitude,
        float $longitude,
        float $headingDegrees,
        float $speedKnots,
        string $locationTimestamp,
        float $courseOverGroundDegrees
    ) {
        $this->imo = $imo;
        $this->mmsi = $mmsi;
        $this->vessel_name = $vesselName;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->heading_degrees = $headingDegrees;
        $this->speed_knots = $speedKnots;
        $this->location_timestamp = $locationTimestamp;
        $this->course_over_ground_degrees = $courseOverGroundDegrees;
    }
}
