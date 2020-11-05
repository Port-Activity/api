<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class BerthReservationTypeModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new BerthReservationTypeModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","name","readable_name"]',
            json_encode($model->buildFields())
        );
    }
    public function testBuildingValues(): void
    {
        $model = new BerthReservationTypeModel();
        $model->id = 1;
        $model->name = "vessel_reserved";
        $model->readable_name = "Reserved for vessel";
        $this->assertEquals(
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"name":"vessel_reserved","readable_name":"Reserved for vessel"}',
            json_encode($model->buildValues($model->buildFields()))
        );
    }

    public function testId(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]]);
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);

        $this->assertEquals(1, BerthReservationTypeModel::id("vessel_reserved", $repository));
    }
}
