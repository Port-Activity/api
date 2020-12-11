<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class SeaChartFixedVesselRepositoryTest extends TestCase
{
    public function testGetFixedVessels(): void
    {
        $db = new FakeConnection([["id" => 11, "vessel_name" => "vesselName", "imo" => 1234567]]);
        $repository = new SeaChartFixedVesselRepository();
        $repository->setDb($db);
        $fixedVessels = $repository->getFixedVessels();
        $this->assertEquals(
            '["SELECT * FROM public.sea_chart_fixed_vessel"]',
            json_encode($db->lastQuery())
        );
        $this->assertTrue(isset($fixedVessels));
        $this->assertEquals(count($fixedVessels), 1);
        $this->assertEquals($fixedVessels[0]->vessel_name, "vesselName");
        $this->assertEquals($fixedVessels[0]->imo, 1234567);
        $this->assertEquals($fixedVessels[0]->id, 11);
    }

    public function testGetFixedVesselByImo(): void
    {
        $db = new FakeConnection([["id" => 11, "vessel_name" => "vesselName", "imo" => 1234567]]);
        $repository = new SeaChartFixedVesselRepository();
        $repository->setDb($db);
        $fixedVessel = $repository->getFixedVesselByImo(1234567);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,mmsi,'
            .'vessel_type,vessel_name FROM public.sea_chart_fixed_vessel '
            . 'WHERE imo=? ORDER BY id LIMIT ? OFFSET ?",1234567,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertTrue(isset($fixedVessel));
        $this->assertEquals($fixedVessel->vessel_name, "vesselName");
        $this->assertEquals($fixedVessel->imo, 1234567);
        $this->assertEquals($fixedVessel->id, 11);
    }

    public function testGetFixedVesselByMmsi(): void
    {
        $db = new FakeConnection([["id" => 11, "vessel_name" => "vesselName", "imo" => 1234567]]);
        $repository = new SeaChartFixedVesselRepository();
        $repository->setDb($db);
        $fixedVessel = $repository->getFixedVesselByMmsi(1234567);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,mmsi,'
            .'vessel_type,vessel_name FROM public.sea_chart_fixed_vessel '
            . 'WHERE mmsi=? ORDER BY id LIMIT ? OFFSET ?",1234567,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertTrue(isset($fixedVessel));
        $this->assertEquals($fixedVessel->vessel_name, "vesselName");
        $this->assertEquals($fixedVessel->imo, 1234567);
        $this->assertEquals($fixedVessel->id, 11);
    }
}
