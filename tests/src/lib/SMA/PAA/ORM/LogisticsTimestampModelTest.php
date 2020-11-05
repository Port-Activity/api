<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class LogisticsTimestampModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new LogisticsTimestampModel();
        $this->assertEquals(
            json_encode($model->buildFields()),
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","time","checkpoint","direction","payload"]'
        );
    }
    public function testBuildingValues(): void
    {
        $model = new LogisticsTimestampModel();
        $model->id = 1;
        $model->time = "2019-01-01T10:12:13Z";
        $model->checkpoint = "Charlie 1";
        $model->direction = "Out";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals(
            json_encode($model->buildValues($model->buildFields())),
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"time":"2019-01-01T10:12:13+00:00",'
            . '"checkpoint":"Charlie 1","direction":"Out",'
            . '"payload":"[\"\"]"}'
        );
    }

    public function testSet(): void
    {
        $model = new LogisticsTimestampModel();
        $model->set("timestamp", "checkpoint", "direction", [""]);

        $this->assertEquals($model->time, "timestamp");
        $this->assertEquals($model->checkpoint, "checkpoint");
        $this->assertEquals($model->direction, "direction");
        $this->assertEquals($model->payload, json_encode([""]));
    }
}
