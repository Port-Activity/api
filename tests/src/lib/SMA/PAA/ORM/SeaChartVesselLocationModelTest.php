<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class SeaChartVesselLocationModelTest extends TestCase
{
    public function testSet(): void
    {
        $model = new SeaChartVesselLocationModel();
        $model->set(
            1234567,
            2345678,
            "Vessel",
            40.34,
            14.343,
            124.4,
            3.5,
            "2020-11-02 12:03:05+00",
            245.7
        );
        $this->assertEquals($model->imo, 1234567);
        $this->assertEquals($model->mmsi, 2345678);
        $this->assertEquals($model->vessel_name, "Vessel");
        $this->assertEquals($model->latitude, 40.34);
        $this->assertEquals($model->longitude, 14.343);
        $this->assertEquals($model->heading_degrees, 124.4);
        $this->assertEquals($model->speed_knots, 3.5);
        $this->assertEquals($model->location_timestamp, "2020-11-02 12:03:05+00");
        $this->assertEquals($model->course_over_ground_degrees, 245.7);
    }
}
