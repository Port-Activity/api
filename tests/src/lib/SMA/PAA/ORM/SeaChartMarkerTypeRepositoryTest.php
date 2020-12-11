<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;

final class SeaChartMarkerTypeRepositoryTest extends TestCase
{
    public function testmarkerNameById(): void
    {
        $db = new FakeConnection([["id" => 11, "name" => "marker-name"]]);
        $repository = new SeaChartMarkerTypeRepository();
        $repository->setDb($db);
        $markerName = $repository->markerNameById(1);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,name '
            .'FROM public.sea_chart_marker_type WHERE id=? ORDER BY id LIMIT ? OFFSET ?",1,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertEquals($markerName, "marker-name");
    }
}
