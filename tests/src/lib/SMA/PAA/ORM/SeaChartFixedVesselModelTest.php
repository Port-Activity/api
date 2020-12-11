<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class SeaChartFixedVesselModelTest extends TestCase
{
    public function testSet(): void
    {
        $model = new SeaChartFixedVesselModel();
        $model->set(11, 1234567, 2345678, 2, "Vessel");
        $this->assertEquals($model->id, 11);
        $this->assertEquals($model->imo, 1234567);
        $this->assertEquals($model->mmsi, 2345678);
        $this->assertEquals($model->vessel_type, 2);
        $this->assertEquals($model->vessel_name, "Vessel");
    }
}
