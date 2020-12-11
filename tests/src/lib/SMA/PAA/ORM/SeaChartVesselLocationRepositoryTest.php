<?php
namespace SMA\PAA\ORM;

use PHPUnit\Framework\TestCase;
use SMA\PAA\TOOL\DateTools;
use DateTime;

final class SeaChartVesselLocationRepositoryTest extends TestCase
{
    public function testGetLocations(): void
    {
        $db = new FakeConnection(
            [[
                "id" => 11,
                "imo" => 1234567,
                "latitude" => 40.42,
                "longitude" => 17.34
            ]]
        );
        $repository = new SeaChartVesselLocationRepository();
        $repository->setDb($db);

        putenv("MAP_VESSEL_LOCATION_MAX_AGE_MINUTES=45");
        $dateTools = new DateTools();
        $earliestAllowedLocationTime = new DateTime(
            $dateTools->subIsoDuration($dateTools->now(), "PT45M")
        );
        $locations = $repository->getLocationsByImos([1234567]);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,mmsi,vessel_name,'
            .'latitude,longitude,heading_degrees,speed_knots,location_timestamp,'
            .'course_over_ground_degrees FROM public.sea_chart_vessel_location WHERE imo in (?) '
            .'AND location_timestamp >= ? '
            .'ORDER BY id LIMIT ? OFFSET ?",'
            . '1234567,"' . $earliestAllowedLocationTime->format("Y-m-d\TH:i:sP") . '",1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertEquals(count($locations), 1);
        $this->assertEquals($locations[0]->id, 11);
        $this->assertEquals($locations[0]->imo, 1234567);
        $this->assertEquals($locations[0]->latitude, 40.42);
        $this->assertEquals($locations[0]->longitude, 17.34);
    }

    public function testGetLocationByImo(): void
    {
        $db = new FakeConnection(
            [[
                "id" => 11,
                "imo" => 1234567,
                "latitude" => 40.42,
                "longitude" => 17.34
            ]]
        );
        putenv("MAP_VESSEL_LOCATION_MAX_AGE_MINUTES=10");
        $dateTools = new DateTools();
        $earliestAllowedLocationTime = new DateTime(
            $dateTools->subIsoDuration($dateTools->now(), "PT10M")
        );
        $repository = new SeaChartVesselLocationRepository();
        $repository->setDb($db);
        $location = $repository->getLocationByImo(1234567);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,'
            .'mmsi,vessel_name,latitude,longitude,'
            .'heading_degrees,speed_knots,location_timestamp,course_over_ground_degrees '
            .'FROM public.sea_chart_vessel_location WHERE imo=? '
            .'AND location_timestamp >= ? '
            .'ORDER BY id LIMIT ? OFFSET ?",1234567,"'
            . $earliestAllowedLocationTime->format("Y-m-d\TH:i:sP") . '",1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertTrue(isset($location));
        $this->assertEquals($location->id, 11);
        $this->assertEquals($location->imo, 1234567);
        $this->assertEquals($location->latitude, 40.42);
        $this->assertEquals($location->longitude, 17.34);
    }

    public function testGetLocationByMmsi(): void
    {
        $db = new FakeConnection(
            [[
                "id" => 11,
                "imo" => 1234567,
                "mmsi" => 2345678,
                "latitude" => 40.42,
                "longitude" => 17.34
            ]]
        );
        putenv("MAP_VESSEL_LOCATION_MAX_AGE_MINUTES=400");
        $dateTools = new DateTools();
        $earliestAllowedLocationTime = new DateTime(
            $dateTools->subIsoDuration($dateTools->now(), "PT400M")
        );
        $repository = new SeaChartVesselLocationRepository();
        $repository->setDb($db);
        $location = $repository->getLocationByMmsi(2345678);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,'
            .'mmsi,vessel_name,latitude,longitude,'
            .'heading_degrees,speed_knots,location_timestamp,course_over_ground_degrees '
            .'FROM public.sea_chart_vessel_location WHERE mmsi=? '
            .'AND location_timestamp >= ? '
            .'ORDER BY id LIMIT ? OFFSET ?",2345678,"'
            . $earliestAllowedLocationTime->format("Y-m-d\TH:i:sP") . '",1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertTrue(isset($location));
        $this->assertEquals($location->id, 11);
        $this->assertEquals($location->imo, 1234567);
        $this->assertEquals($location->latitude, 40.42);
        $this->assertEquals($location->longitude, 17.34);
    }
    public function testGetLocationsInvalidAgeRestriction(): void
    {
        $db = new FakeConnection(
            [[
                "id" => 11,
                "imo" => 1234567,
                "latitude" => 40.42,
                "longitude" => 17.34
            ]]
        );
        $repository = new SeaChartVesselLocationRepository();
        $repository->setDb($db);

        putenv("MAP_VESSEL_LOCATION_MAX_AGE_MINUTES=long");
        $locations = $repository->getLocationsByImos([1234567]);
        $this->assertEquals(
            '["SELECT id,created_at,created_by,modified_at,modified_by,imo,mmsi,vessel_name,'
            .'latitude,longitude,heading_degrees,speed_knots,location_timestamp,'
            .'course_over_ground_degrees FROM public.sea_chart_vessel_location WHERE imo in (?) '
            .'ORDER BY id LIMIT ? OFFSET ?",'
            . '1234567,1,0]',
            json_encode($db->lastQuery())
        );
        $this->assertEquals(count($locations), 1);
        $this->assertEquals($locations[0]->id, 11);
        $this->assertEquals($locations[0]->imo, 1234567);
        $this->assertEquals($locations[0]->latitude, 40.42);
        $this->assertEquals($locations[0]->longitude, 17.34);
    }
}
