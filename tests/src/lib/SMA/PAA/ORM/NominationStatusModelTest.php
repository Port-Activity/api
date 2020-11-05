<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class NominationStatusModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new NominationStatusModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","name","readable_name"]',
            json_encode($model->buildFields())
        );
    }
    public function testBuildingValues(): void
    {
        $model = new NominationStatusModel();
        $model->id = 1;
        $model->name = "open";
        $model->readable_name = "Open";
        $this->assertEquals(
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"name":"open","readable_name":"Open"}',
            json_encode($model->buildValues($model->buildFields()))
        );
    }

    public function testId(): void
    {
        $db = new FakeConnection([["id" => 1, "name" => "open", "readable_name" => "Open"]]);
        $repository = new NominationStatusRepository();
        $repository->setDb($db);

        $this->assertEquals(1, NominationStatusModel::id("open", $repository));
    }
}
