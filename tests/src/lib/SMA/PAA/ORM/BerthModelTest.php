<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class BerthModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new BerthModel();
        $this->assertEquals(
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","code","name","nominatable"]',
            json_encode($model->buildFields())
        );
    }
    public function testBuildingValues(): void
    {
        $model = new BerthModel();
        $model->id = 1;
        $model->code = "B1";
        $model->name = "Berth 1";
        $this->assertEquals(
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"code":"B1","name":"Berth 1","nominatable":"f"}',
            json_encode($model->buildValues($model->buildFields()))
        );
    }

    public function testSet(): void
    {
        $model = new BerthModel();
        $model->set("B1", "Berth 1", true);
        $this->assertEquals("B1", $model->code);
        $this->assertEquals("Berth 1", $model->name);
        $this->assertEquals(true, $model->getIsNominatable());
    }
}
