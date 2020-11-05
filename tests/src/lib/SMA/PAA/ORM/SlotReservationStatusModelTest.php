<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class SlotReservationStatusModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new SlotReservationStatusModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","name","readable_name"]',
            json_encode($model->buildFields())
        );
    }
    public function testBuildingValues(): void
    {
        $model = new SlotReservationStatusModel();
        $model->id = 1;
        $model->name = "requested";
        $model->readable_name = "Requested";
        $this->assertEquals(
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"name":"requested","readable_name":"Requested"}',
            json_encode($model->buildValues($model->buildFields()))
        );
    }

    public function testId(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "requested", "readable_name" => "Requested"]]);
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);

        $this->assertEquals(1, SlotReservationStatusModel::id("requested", $repository));
    }
}
