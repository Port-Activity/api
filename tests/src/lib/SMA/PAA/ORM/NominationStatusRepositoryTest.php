<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class NominationStatusRepositoryTest extends TestCase
{
    public function testGetStatusNameMappingsSqlQuery(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "open", "readable_name" => "Open"]
                ,["id" => 2, "name" => "reserved", "readable_name" => "Reserved"]
                ,["id" => 3, "name" => "expired", "readable_name" => "Expired"]
            ]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $repository->getStatusNameMappings();
        $this->assertEquals(
            '["SELECT * FROM public.nomination_status ORDER BY id"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetStatusNameMappingsReturnValue(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "open", "readable_name" => "Open"]
                ,["id" => 2, "name" => "reserved", "readable_name" => "Reserved"]
                ,["id" => 3, "name" => "expired", "readable_name" => "Expired"]
            ]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $result = $repository->getStatusNameMappings();
        $this->assertEquals(
            ["open" => 1, "reserved" => 2, "expired" => 3],
            $result
        );
    }

    public function testMapStatusNameToStatusIdSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "open", "readable_name" => "Open"]]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $repository->mapStatusNameToStatusId("open");
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.nomination_status '
            . 'WHERE name=? ORDER BY id LIMIT ? OFFSET ?","open",1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusNameToStatusIdReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "open", "readable_name" => "Open"]]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusNameToStatusId("open");
        $this->assertEquals(
            1,
            $result
        );
    }

    public function testMapStatusIdToStatusNameSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "open", "readable_name" => "Open"]]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.nomination_status '
            . 'WHERE id=? ORDER BY id LIMIT ? OFFSET ?",1,1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusIdToStatusNameReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "open", "readable_name" => "Open"]]
        );
        $repository = new NominationStatusRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            "open",
            $result
        );
    }
}
