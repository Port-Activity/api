<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class TimestampTimeTypeModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new TimestampTimeTypeModel();
        $this->assertEquals(
            json_encode($model->buildFields()),
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","name"]'
        );
    }
    public function testBuildingValues(): void
    {
        $model = new TimestampTimeTypeModel();
        $model->id = 1;
        $model->name = "Foo";
        $this->assertEquals(
            json_encode($model->buildValues($model->buildFields())),
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"name":"Foo"}'
        );
    }
}
