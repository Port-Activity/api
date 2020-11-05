<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class TimestampRepositoryTest extends TestCase
{
    public function testIsDuplicateSqlQuery(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $model = new TimestampModel();
        $model->created_by = 1;
        $model->imo = 1234567;
        $model->vessel_name = "vessel_name";
        $model->time_type_id = 1;
        $model->state_id = 2;
        $model->time = "timestamp";
        $payload = [""];
        $model->payload = json_encode($payload);
        $repository->isDuplicate($model);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,'
            . 'modified_at,modified_by,imo,vessel_name,time_type_id,state_id,time,payload,port_call_id,'
            . 'is_trash,weight '
            . 'FROM public.timestamp '
            . 'WHERE imo=? AND vessel_name=? AND time_type_id=? AND state_id=? AND time=? AND payload=? '
            . 'ORDER BY id LIMIT ? OFFSET ?",1234567,"vessel_name",1,2,"timestamp","[\"\"]",1,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testIsDuplicateTrue(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "imo" => 1234567,
            "vessel_name" => "vessel_name",
            "time_type_id" => 1,
            "state_id" => 2,
            "time" => "timestamp",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $model = new TimestampModel();
        $model->created_by = 1;
        $model->imo = 1234567;
        $model->vessel_name = "vessel_name";
        $model->time_type_id = 1;
        $model->state_id = 2;
        $model->time = "timestamp";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals($repository->isDuplicate($model), true);
    }
    public function testIsDuplicateFalse(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $model = new TimestampModel();
        $model->created_by = 1;
        $model->imo = 1234567;
        $model->vessel_name = "vessel_name";
        $model->time_type_id = 1;
        $model->state_id = 2;
        $model->time = "timestamp";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals($repository->isDuplicate($model), false);
    }
    public function testGetAllTimestampsSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "imo" => 1234567,
            "vessel_name" => "vessel_name",
            "time_type_id" => 1,
            "state_id" => 2,
            "time" => "timestamp",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $repository->getAllTimestamps(10);
        $this->assertEquals(
            '["SELECT * FROM public.timestamp '
            . 'ORDER BY time DESC '
            . 'LIMIT \'10\'"]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetAllTimestampsReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "imo" => 1234567,
            "vessel_name" => "vessel_name",
            "time_type_id" => 1,
            "state_id" => 2,
            "time" => "timestamp",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $res = $repository->getAllTimestamps(10);
        $this->assertEquals($res[0]->imo, 1234567);
        $this->assertEquals($res[0]->vessel_name, "vessel_name");
        $this->assertEquals($res[0]->time_type_id, 1);
        $this->assertEquals($res[0]->state_id, 2);
        $this->assertEquals($res[0]->time, "timestamp");
        $this->assertEquals($res[0]->payload, json_encode([""]));
        $this->assertEquals($res[0]->created_by, 1);
        $this->assertEquals($res[0]->modified_by, 1);
    }
    public function testListSqlQuery1(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $repository->list([], 0, 0);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,'
            . 'imo,vessel_name,time_type_id,state_id,time,payload,port_call_id,is_trash,weight '
            . 'FROM public.timestamp ORDER BY id LIMIT ? OFFSET ?",0,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testListSqlQuery2(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $repository->list(["test_equals" => 1], 0, 0);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,'
            . 'imo,vessel_name,time_type_id,state_id,time,payload,port_call_id,is_trash,weight '
            . 'FROM public.timestamp '
            . 'WHERE test_equals=? '
            . 'ORDER BY id LIMIT ? OFFSET ?",1,0,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testListSqlQuery3(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $repository->list(["test_equals" => 1, "test_gt" => ["gt" => 2]], 0, 0);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,'
            . 'imo,vessel_name,time_type_id,state_id,time,payload,port_call_id,is_trash,weight '
            . 'FROM public.timestamp '
            . 'WHERE test_equals=? AND test_gt > ? '
            . 'ORDER BY id LIMIT ? OFFSET ?",1,2,0,0]',
            json_encode($db->lastQuery())
        );
    }
    public function testListSqlQuery4(): void
    {
        $db = new FakeConnection([]);
        $repository = new TimestampRepository();
        $repository->setDb($db);
        $repository->list(
            ["test_equals" => 1,
            "test_gt" => ["gt" => 2],
            "test_lte_gte" => ["lte" => 3, "gte" => 4]],
            0,
            0
        );
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,'
            . 'imo,vessel_name,time_type_id,state_id,time,payload,port_call_id,is_trash,weight '
            . 'FROM public.timestamp '
            . 'WHERE test_equals=? AND test_gt > ? AND test_lte_gte <= ? AND test_lte_gte >= ? '
            . 'ORDER BY id LIMIT ? OFFSET ?",1,2,3,4,0,0]',
            json_encode($db->lastQuery())
        );
    }
}
