<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;
use SMA\PAA\ORM\VesselTypeModel;
use SMA\PAA\InvalidParameterException;

final class VesselTypeRepositoryTest extends TestCase
{
    public function testGetMarkerType(): void
    {
        $db = new FakeConnection([["id" => 11]]);
        $repository = new VesselTypeRepository();
        $repository->setDb($db);

        $markerType = $repository->getMarkerType(11, 8);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,sea_chart_marker_type_id '
            .'FROM public.vessel_type WHERE id=? ORDER BY id LIMIT ? OFFSET ?",11,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertEquals($markerType, 8);
    }

    public function testSave(): void
    {
        $db = new FakeConnection([["id" => 11]]);
        $repository = new VesselTypeRepository();
        $repository->setDb($db);

        $vesselType = new VesselTypeModel();
        $vesselType->name = 'New Vessel Type';
        $vesselType->sea_chart_marker_type_id = 5;

        $id = $repository->save($vesselType, true);

        $this->assertTrue(
            strpos(
                json_encode($db->lastQuery()),
                '"INSERT INTO public.vessel_type (created_at,created_by,modified_at,modified_by,'
                .'name,sea_chart_marker_type_id) VALUES (?,?,?,?,?,?)"'
            ) !== false
        );
        $this->assertTrue(strpos(json_encode($db->lastQuery()), '"New Vessel Type",5') !== false);
        $this->assertEquals(123, $id);
    }

    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Mandatory vessel type properties missing
     */
    public function testSaveFailure(): void
    {
        $db = new FakeConnection();
        $repository = new VesselTypeRepository();
        $repository->setDb($db);

        $vesselType = new VesselTypeModel();
        $repository->save($vesselType, true);
    }

    public function testGetVesselTypeName(): void
    {
        $db = new FakeConnection([["id" => 11]]);
        $repository = new VesselTypeRepository();
        $repository->setDb($db);

        $typeName = $repository->getVesselTypeName(11);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name,sea_chart_marker_type_id '
            .'FROM public.vessel_type WHERE id=? ORDER BY id LIMIT ? OFFSET ?",11,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertEquals($typeName, "");
    }
}
