<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

class TimestampModelTest extends TestCase
{
    public function testBuildingFields(): void
    {
        $model = new TimestampModel();
        $this->assertEquals(
            json_encode($model->buildFields()),
            '["id","created_at","created_by","modified_at",'
            . '"modified_by","imo","vessel_name","time_type_id",'
            . '"state_id","time","payload","port_call_id","is_trash","weight"]'
        );
    }
    public function testBuildingValues(): void
    {
        $model = new TimestampModel();
        $model->id = 1;
        $model->imo = 1234567;
        $model->vessel_name = "Vessel";
        $model->time_type_id = 1;
        $model->state_id = 2;
        $model->time = "2019-01-01T10:12:13Z";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals(
            json_encode($model->buildValues($model->buildFields())),
            '{"id":1,"created_at":null,"created_by":null,"modified_at":null,'
            . '"modified_by":null,"imo":1234567,"vessel_name":"Vessel","time_type_id":1,'
            . '"state_id":2,"time":"2019-01-01T10:12:13+00:00","payload":"[\"\"]","port_call_id":null,'
            . '"is_trash":"f","weight":0}'
        );
    }

    public function testSet(): void
    {
        $GLOBALS["SMA\PAA\ORM\TimestampTimeTypeRepository_fake_db"] =
            new FakeConnection([["id" => 1, "name" => "timetype1"], ["id" => 2, "name" => "timetype2"]]);
        $GLOBALS["SMA\PAA\ORM\TimestampStateRepository_fake_db"] =
            new FakeConnection([["id" => 1, "name" => "statetype1"], ["id" => 2, "name" => "statetype2"]]);
        $model = new TimestampModel();
        $model->set(1234567, "Vessel", "timetype1", "statetype2", "timestamp", [""]);
        unset($GLOBALS["SMA\PAA\ORM\TimestampTimeTypeRepository_fake_db"]);
        unset($GLOBALS["SMA\PAA\ORM\TimestampStateRepository_fake_db"]);
        $this->assertEquals($model->imo, 1234567);
        $this->assertEquals($model->vessel_name, "Vessel");
        $this->assertEquals($model->time_type_id, 1);
        $this->assertEquals($model->state_id, 2);
        $this->assertEquals($model->time, "timestamp");
        $this->assertEquals($model->payload, json_encode([""]));
    }
}
