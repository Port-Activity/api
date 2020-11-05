<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class SeaChartServiceTest extends TestCase
{
    public function testVessels(): void
    {
        $service = new SeaChartServiceExt();
        $vessels = $service->vessels();
        $this->assertFalse(empty($vessels));
    }
    public function testMarkers(): void
    {
        $service = new SeaChartServiceExt();
        $markers = $service->markers();
        $this->assertTrue(empty($markers));
    }
    public function testUpdateVesselLocations(): void
    {
        $service = new SeaChartServiceExt();
        $updateData = array();
        $updateData[] = array("imo" => "9552020",
            "mmsi" => "255805989",
            "latitude" => "61.207779",
            "longitude" => "18.229229",
            "heading" => "172.4",
            "speed_knots" => "5.3"
        );
 
        $res = $service->updateVesselLocations($updateData);
        $this->assertFalse(empty($res));
        $this->assertTrue($res["result"] === "OK");
    }
}
