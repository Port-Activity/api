<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class BerthReservationTypeRepositoryTest extends TestCase
{
    public function testGetStatusNameMappingsSqlQuery(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]
                ,["id" => 2, "name" => "port_blocked", "readable_name" => "Blocked by port"]
            ]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $repository->getStatusNameMappings();
        $this->assertEquals(
            '["SELECT * FROM public.berth_reservation_type ORDER BY id"]',
            json_encode($db->lastQuery())
        );
    }

    public function testGetStatusNameMappingsReturnValue(): void
    {
        $db = new FakeConnection(
            [
                ["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]
                ,["id" => 2, "name" => "port_blocked", "readable_name" => "Blocked by port"]
            ]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $result = $repository->getStatusNameMappings();
        $this->assertEquals(
            ["vessel_reserved" => 1, "port_blocked" => 2],
            $result
        );
    }

    public function testMapStatusNameToStatusIdSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $repository->mapStatusNameToStatusId("vessel_reserved");
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.berth_reservation_type '
            . 'WHERE name=? ORDER BY id LIMIT ? OFFSET ?","vessel_reserved",1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusNameToStatusIdReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusNameToStatusId("vessel_reserved");
        $this->assertEquals(
            1,
            $result
        );
    }

    public function testMapStatusIdToStatusNameSqlQuery(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,readable_name '
            . 'FROM public.berth_reservation_type '
            . 'WHERE id=? ORDER BY id LIMIT ? OFFSET ?",1,1,0]',
            json_encode($db->lastQuery())
        );
    }

    public function testMapStatusIdToStatusNameReturnValue(): void
    {
        $db = new FakeConnection(
            [["id" => 1, "name" => "vessel_reserved", "readable_name" => "Reserved for vessel"]]
        );
        $repository = new BerthReservationTypeRepository();
        $repository->setDb($db);
        $result = $repository->mapStatusIdToStatusName(1);
        $this->assertEquals(
            "vessel_reserved",
            $result
        );
    }
}
