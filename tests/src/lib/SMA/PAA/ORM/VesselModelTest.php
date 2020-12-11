<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class VesselModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new VesselModel();
        $this->assertEquals(
            json_encode($model->buildFields()),
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","imo","vessel_name","visible","vessel_type"]'
        );
    }
    public function testBuildingValues(): void
    {
        $model = new VesselModel();
        $model->id = 1;
        $model->imo = 1234567;
        $model->vessel_name = "Vessel";
        $model->vessel_type = 5;
        $this->assertEquals(
            json_encode($model->buildValues($model->buildFields())),
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"imo":1234567,"vessel_name":"Vessel","visible":"t","vessel_type":5}'
        );
    }

    public function testSet(): void
    {
        $model = new VesselModel();
        $model->set(1234567, "Vessel", true);
        $this->assertEquals($model->imo, 1234567);
        $this->assertEquals($model->vessel_name, "Vessel");
        $this->assertEquals($model->getIsVisible(), true);
    }
}
