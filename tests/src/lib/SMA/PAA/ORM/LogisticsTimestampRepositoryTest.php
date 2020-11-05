<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class LogisticsTimestampRepositoryTest extends TestCase
{
    public function testIsDuplicateSqlQuery(): void
    {
        $db = new FakeConnection([]);
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $model = new LogisticsTimestampModel();
        $model->created_by = 1;
        $model->time = "timestamp";
        $model->checkpoint = "checkpoint";
        $model->direction = "direction";
        $payload = [""];
        $model->payload = json_encode($payload);
        $repository->isDuplicate($model);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,'
            . 'modified_at,modified_by,time,checkpoint,direction,payload '
            . 'FROM public.logistics_timestamp '
            . 'WHERE time=? AND checkpoint=? AND direction=? AND payload=? '
            . 'ORDER BY id LIMIT ? OFFSET ?","timestamp","checkpoint","direction","[\"\"]",1,0]',
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
            "time" => "timestamp",
            "checkpoint" => "direction",
            "direction" => "direction",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $model = new LogisticsTimestampModel();
        $model->created_by = 1;
        $model->time = "timestamp";
        $model->checkpoint = "checkpoint";
        $model->direction = "direction";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals($repository->isDuplicate($model), true);
    }
    public function testIsDuplicateFalse(): void
    {
        $db = new FakeConnection([]);
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $model = new LogisticsTimestampModel();
        $model->created_by = 1;
        $model->time = "timestamp";
        $model->checkpoint = "checkpoint";
        $model->direction = "direction";
        $payload = [""];
        $model->payload = json_encode($payload);
        $this->assertEquals($repository->isDuplicate($model), false);
    }
    public function testGetAllLogisticsTimestampsQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "time" => "timestamp",
            "checkpoint" => "direction",
            "direction" => "direction",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $repository->getAllLogisticsTimestamps(10);
        $this->assertEquals(
            '["SELECT * FROM public.logistics_timestamp '
            . 'ORDER BY time DESC '
            . 'LIMIT \'10\'"]',
            json_encode($db->lastQuery())
        );
    }
    public function testGetAllLogisticsTimestampsReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "time" => "timestamp",
            "checkpoint" => "checkpoint",
            "direction" => "direction",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $res = $repository->getAllLogisticsTimestamps(10);
        $this->assertEquals($res[0]->time, "timestamp");
        $this->assertEquals($res[0]->checkpoint, "checkpoint");
        $this->assertEquals($res[0]->direction, "direction");
        $this->assertEquals($res[0]->payload, json_encode([""]));
        $this->assertEquals($res[0]->created_by, 1);
        $this->assertEquals($res[0]->modified_by, 1);
    }
    public function testGetLogisticsTimestampsQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "time" => "timestamp",
            "checkpoint" => "direction",
            "direction" => "direction",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $repository->getLogisticsTimestamps("ABC123");

        $this->assertEquals(
            str_replace("\n", "", <<<EOF
["SELECT * FROM public.logistics_timestamp l,
 jsonb_array_elements(replace(l.payload->>'front_license_plates',
 '[]', '[1]')::jsonb) f, jsonb_array_elements(replace(l.payload->>'rear_license_plates',
 '[]', '[1]')::jsonb) r WHERE (f->>'number' in (?) OR r->>'number' in (?))
 ORDER BY time DESC LIMIT 100","ABC123","ABC123"]
EOF),
            json_encode($db->lastQuery())
        );
    }
    public function testGetLogisticsTimestampsReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1,
            "created_at" => "dummy",
            "created_by" => 1,
            "modified_at" => "dummy",
            "modified_by" => 1,
            "time" => "timestamp",
            "checkpoint" => "checkpoint",
            "direction" => "direction",
            "payload" => json_encode([""])
            ]]
        );
        $repository = new LogisticsTimestampRepository();
        $repository->setDb($db);
        $res = $repository->getLogisticsTimestamps("ABC123");
        $this->assertEquals($res[0]->time, "timestamp");
        $this->assertEquals($res[0]->checkpoint, "checkpoint");
        $this->assertEquals($res[0]->direction, "direction");
        $this->assertEquals($res[0]->payload, json_encode([""]));
        $this->assertEquals($res[0]->created_by, 1);
        $this->assertEquals($res[0]->modified_by, 1);
    }
}
