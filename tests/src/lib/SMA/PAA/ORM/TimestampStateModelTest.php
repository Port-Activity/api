<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class TimestampStateModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new TimestampStateModel();
        $this->assertEquals(
            json_encode($model->buildFields()),
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","name","state_type_id","description"]'
        );
    }
    public function testBuildingValues(): void
    {
        $model = new TimestampStateModel();
        $model->id = 1;
        $model->name = "Foo";
        $model->state_type_id = 2;
        $model->description = "Bar";
        $this->assertEquals(
            json_encode($model->buildValues($model->buildFields())),
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"name":"Foo","state_type_id":2,"description":"Bar"}'
        );
    }
}
