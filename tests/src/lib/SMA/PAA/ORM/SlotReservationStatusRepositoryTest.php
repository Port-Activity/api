<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class SlotReservationStatusRepositoryTest extends TestCase
{
    public function testGetStatusNameMappingsSqlQuery(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "requested", "readable_name" => "Requested"]
                ,["id" => 2, "name" => "offered", "readable_name" => "Offered"]
                ,["id" => 3, "name" => "accepted", "readable_name" => "Accepted"]
            ]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $repository->getStatusNameMappings();
        $this->assertEquals(
            '["SELECT * FROM public.slot_reservation_status ORDER BY id"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetStatusNameMappingsReturnValue(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "requested", "readable_name" => "Requested"]
                ,["id" => 2, "name" => "offered", "readable_name" => "Offered"]
                ,["id" => 3, "name" => "accepted", "readable_name" => "Accepted"]
            ]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $result = $repository->getStatusNameMappings();
        $this->assertEquals(
            ["requested" => 1, "offered" => 2, "accepted" => 3],
            $result
        );
    }

    public function testMapStatusNameToStatusIdSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "requested", "readable_name" => "Requested"]]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $repository->mapStatusNameToStatusId("requested");
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.slot_reservation_status '
            . 'WHERE name=? ORDER BY id LIMIT ? OFFSET ?","requested",1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusNameToStatusIdReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "requested", "readable_name" => "Requested"]]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusNameToStatusId("requested");
        $this->assertEquals(
            1,
            $result
        );
    }

    public function testMapStatusIdToStatusNameSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "requested", "readable_name" => "Requested"]]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.slot_reservation_status '
            . 'WHERE id=? ORDER BY id LIMIT ? OFFSET ?",1,1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusIdToStatusNameReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "requested", "readable_name" => "Requested"]]
        );
        $repository = new SlotReservationStatusRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            "requested",
            $result
        );
    }
}
