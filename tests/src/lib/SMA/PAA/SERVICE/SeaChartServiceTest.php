<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\InvalidParameterException;

final class SeaChartServiceTest extends TestCase
{
    public function testVessels(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 111111],
            ["imo" => 999999],
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;

        $vessels = $service->vessels();
        $this->assertFalse(empty($vessels));
        $this->assertEquals(3, count($vessels));

        // Fixed vessel entry (data is partially fixed by stub)
        $this->assertEquals("Feature", $vessels[0]["type"]);
        $this->assertFalse(empty($vessels[0]["geometry"]));
        $this->assertEquals("Point", $vessels[0]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[0]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[0]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[0]["properties"]));
        $this->assertEquals(222222, $vessels[0]["properties"]["mmsi"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[0]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[0]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[0]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[0]["properties"]["heading_degrees"]);
        $this->assertEquals("markerTypeFixed", $vessels[0]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[0]["properties"]["location_timestamp"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[0]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 2", $vessels[0]["properties"]["vessel_type_name"]);

        // Timeline entries (data is partially fixed by stub)
        $this->assertEquals("Feature", $vessels[1]["type"]);
        $this->assertFalse(empty($vessels[1]["geometry"]));
        $this->assertEquals("Point", $vessels[1]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[1]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[1]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[1]["properties"]));
        $this->assertEquals(111111, $vessels[1]["properties"]["mmsi"]);
        $this->assertEquals(111111, $vessels[1]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[1]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[1]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[1]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[1]["properties"]["heading_degrees"]);
        $this->assertEquals("markerType11", $vessels[1]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[1]["properties"]["location_timestamp"]);
        $this->assertEquals(111111, $vessels[1]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[1]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 1", $vessels[1]["properties"]["vessel_type_name"]);

        $this->assertEquals("Feature", $vessels[2]["type"]);
        $this->assertFalse(empty($vessels[2]["geometry"]));
        $this->assertEquals("Point", $vessels[2]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[2]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[2]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[2]["properties"]));
        $this->assertEquals(999999, $vessels[2]["properties"]["mmsi"]);
        $this->assertEquals(999999, $vessels[2]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[2]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[2]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[2]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[2]["properties"]["heading_degrees"]);
        $this->assertEquals("markerTypeUnknown", $vessels[2]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[2]["properties"]["location_timestamp"]);
        $this->assertEquals(999999, $vessels[2]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[2]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Uknown Vessel Type", $vessels[2]["properties"]["vessel_type_name"]);
    }
    public function testVesselsWithDuplicates(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 111111],
            ["imo" => 111111]
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;

        $vessels = $service->vessels();
        $this->assertFalse(empty($vessels));
        $this->assertTrue(count($vessels) === 2); // IMO 111111 + fixed

        // Fixed vessel entry (data is partially fixed by stub)
        $this->assertEquals("Feature", $vessels[0]["type"]);
        $this->assertFalse(empty($vessels[0]["geometry"]));
        $this->assertEquals("Point", $vessels[0]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[0]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[0]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[0]["properties"]));
        $this->assertEquals(222222, $vessels[0]["properties"]["mmsi"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[0]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[0]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[0]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[0]["properties"]["heading_degrees"]);
        $this->assertEquals("markerTypeFixed", $vessels[0]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[0]["properties"]["location_timestamp"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[0]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 2", $vessels[0]["properties"]["vessel_type_name"]);

        // Timeline entries (data is partially fixed by stub)
        $this->assertEquals("Feature", $vessels[1]["type"]);
        $this->assertFalse(empty($vessels[1]["geometry"]));
        $this->assertEquals("Point", $vessels[1]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[1]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[1]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[1]["properties"]));
        $this->assertEquals(111111, $vessels[1]["properties"]["mmsi"]);
        $this->assertEquals(111111, $vessels[1]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[1]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[1]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[1]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[1]["properties"]["heading_degrees"]);
        $this->assertEquals("markerType11", $vessels[1]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[1]["properties"]["location_timestamp"]);
        $this->assertEquals(111111, $vessels[1]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[1]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 1", $vessels[1]["properties"]["vessel_type_name"]);
    }
    public function testVesselsFixedVesselTypeNotDefined(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 222222],
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;
        $service->fakeFixedVesselRepository->returnVesselTypeWithFixedVessel = false;

        $vessels = $service->vessels();
        $this->assertFalse(empty($vessels));

        $this->assertEquals("Feature", $vessels[0]["type"]);
        $this->assertFalse(empty($vessels[0]["geometry"]));
        $this->assertEquals("Point", $vessels[0]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[0]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[0]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[0]["properties"]));
        $this->assertEquals(222222, $vessels[0]["properties"]["mmsi"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[0]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[0]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[0]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[0]["properties"]["heading_degrees"]);
        $this->assertEquals("markerType12", $vessels[0]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[0]["properties"]["location_timestamp"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[0]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 2", $vessels[0]["properties"]["vessel_type_name"]);
    }
    public function testVesselsPortcallVesselInFixedVessels(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 222222]
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;

        $vessels = $service->vessels();
        $this->assertFalse(empty($vessels));
        $this->assertTrue(count($vessels) === 1); // IMO 111111

        $this->assertEquals("Feature", $vessels[0]["type"]);
        $this->assertFalse(empty($vessels[0]["geometry"]));
        $this->assertEquals("Point", $vessels[0]["geometry"]["type"]);
        $this->assertEquals(-24.534348, $vessels[0]["geometry"]["coordinates"][0]);
        $this->assertEquals(55.973345, $vessels[0]["geometry"]["coordinates"][1]);
        $this->assertFalse(empty($vessels[0]["properties"]));
        $this->assertEquals(222222, $vessels[0]["properties"]["mmsi"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["imo"]);
        $this->assertEquals(55.973345, $vessels[0]["properties"]["latitude"]);
        $this->assertEquals(-24.534348, $vessels[0]["properties"]["longitude"]);
        $this->assertEquals(16.4, $vessels[0]["properties"]["speed_knots"]);
        $this->assertEquals(180.0, $vessels[0]["properties"]["heading_degrees"]);
        $this->assertEquals("markerTypeFixed", $vessels[0]["properties"]["marker_class"]);
        $this->assertEquals("2020-11-02T12:03:05+00:00", $vessels[0]["properties"]["location_timestamp"]);
        $this->assertEquals(222222, $vessels[0]["properties"]["id"]);
        $this->assertEquals(140.0, $vessels[0]["properties"]["course_over_ground_degrees"]);
        $this->assertEquals("Vessel type desc 2", $vessels[0]["properties"]["vessel_type_name"]);
    }
    public function testMarkersInvalidNameSpace(): void
    {
        $service = new SeaChartServiceExt();
        putenv("NAMESPACE=MoonRepublic");
        $markers = $service->markers();
        $this->assertTrue(empty($markers));
    }
    public function testMarkers(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 111111],
            ["imo" => 999999]
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;

        putenv("MAP_MARKERS=".file_get_contents(
            __DIR__ . "/sea-chart-data/test_data_sea_chart_markers.json"
        ));

        $markers = $service->markers();

        $this->assertEquals(5, count($markers));

        $this->assertEquals("Feature", $markers[0]["type"]);
        $this->assertTrue(key_exists("geometry", $markers[0]));
        $this->assertEquals("Point", $markers[0]["geometry"]["type"]);
        $this->assertTrue(key_exists("coordinates", $markers[0]["geometry"]));
        $this->assertEquals(2, count($markers[0]["geometry"]["coordinates"]));
        $this->assertEquals(17.2, $markers[0]["geometry"]["coordinates"][0]);
        $this->assertEquals(60.6865, $markers[0]["geometry"]["coordinates"][1]);
        $this->assertTrue(key_exists("properties", $markers[0]));
        $this->assertEquals("Port of Gävle", $markers[0]["properties"]["name"]);
        $this->assertEquals("The hub of the East coast", $markers[0]["properties"]["description"]);
        $this->assertEquals("port", $markers[0]["properties"]["type"]);
        $this->assertFalse(empty($markers[0]["properties"]["id"]));

        $this->assertEquals("Feature", $markers[1]["type"]);
        $this->assertTrue(key_exists("geometry", $markers[1]));
        $this->assertEquals("Polygon", $markers[1]["geometry"]["type"]);
        $this->assertEquals(1, count($markers[1]["geometry"]["coordinates"]));
        $this->assertEquals(19, count($markers[1]["geometry"]["coordinates"][0]));
        $this->assertEquals(2, count($markers[1]["geometry"]["coordinates"][0][0]));
        $this->assertEquals(17.311872, $markers[1]["geometry"]["coordinates"][0][0][0]);
        $this->assertEquals(60.616235, $markers[1]["geometry"]["coordinates"][0][0][1]);
        $this->assertEquals("Gävle outer port area", $markers[1]["properties"]["name"]);
        $this->assertEquals("Outer port area of the port", $markers[1]["properties"]["description"]);
        $this->assertEquals("outer-port-area", $markers[1]["properties"]["type"]);
        $this->assertFalse(empty($markers[1]["properties"]["id"]));

        $this->assertEquals("Feature", $markers[2]["type"]);
        $this->assertTrue(key_exists("geometry", $markers[2]));
        $this->assertEquals("LineString", $markers[2]["geometry"]["type"]);
        $this->assertTrue(key_exists("coordinates", $markers[2]["geometry"]));
        $this->assertEquals(2, count($markers[2]["geometry"]["coordinates"]));
        $this->assertEquals(2, count($markers[2]["geometry"]["coordinates"][0]));
        $this->assertEquals(17.302517, $markers[2]["geometry"]["coordinates"][0][0]);
        $this->assertEquals(60.728314, $markers[2]["geometry"]["coordinates"][0][1]);
        $this->assertEquals(2, count($markers[2]["geometry"]["coordinates"][1]));
        $this->assertEquals(17.316336, $markers[2]["geometry"]["coordinates"][1][0]);
        $this->assertEquals(60.720339, $markers[2]["geometry"]["coordinates"][1][1]);
        $this->assertEquals("Holmudds channel line", $markers[2]["properties"]["name"]);
        $this->assertEquals("Line in channel", $markers[2]["properties"]["description"]);
        $this->assertEquals("channel-line", $markers[2]["properties"]["type"]);
        $this->assertFalse(empty($markers[2]["properties"]["id"]));

        $this->assertEquals("Feature", $markers[3]["type"]);
        $this->assertTrue(key_exists("geometry", $markers[3]));
        $this->assertEquals("Point", $markers[3]["geometry"]["type"]);
        $this->assertTrue(key_exists("coordinates", $markers[3]["geometry"]));
        $this->assertEquals(2, count($markers[3]["geometry"]["coordinates"]));
        $this->assertEquals(17.228745, $markers[3]["geometry"]["coordinates"][0]);
        $this->assertEquals(60.695022, $markers[3]["geometry"]["coordinates"][1]);
        $this->assertTrue(key_exists("properties", $markers[3]));
        $this->assertEquals("Berth", $markers[3]["properties"]["name"]);
        $this->assertEquals("Very nice berth", $markers[3]["properties"]["description"]);
        $this->assertEquals("berth", $markers[3]["properties"]["type"]);
        $this->assertFalse(empty($markers[3]["properties"]["id"]));

        $this->assertEquals("Feature", $markers[4]["type"]);
        $this->assertTrue(key_exists("geometry", $markers[4]));
        $this->assertEquals("Point", $markers[4]["geometry"]["type"]);
        $this->assertTrue(key_exists("coordinates", $markers[4]["geometry"]));
        $this->assertEquals(2, count($markers[4]["geometry"]["coordinates"]));
        $this->assertEquals(17.403186, $markers[4]["geometry"]["coordinates"][0]);
        $this->assertEquals(60.699286, $markers[4]["geometry"]["coordinates"][1]);
        $this->assertTrue(key_exists("properties", $markers[4]));
        $this->assertEquals("Anchorage", $markers[4]["properties"]["name"]);
        $this->assertEquals("Anchorage here", $markers[4]["properties"]["description"]);
        $this->assertEquals("anchorage", $markers[4]["properties"]["type"]);
        $this->assertFalse(empty($markers[4]["properties"]["id"]));
    }

    public function testMarkerIdUniqueness(): void
    {
        $service = new SeaChartServiceExt();

        $portCalls = array(
            ["imo" => 1],
            ["imo" => 2],
            ["imo" => 3],
            ["imo" => 4]
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;
        $service->fakeFixedVesselRepository->returnFixedVessel = false;

        putenv("SEA_CHART_ID_RANGE_MAX=7");

        putenv("MAP_MARKERS=".file_get_contents(
            __DIR__ . "/sea-chart-data/test_data_sea_chart_markers.json"
        ));

        $vessels = $service->vessels();
        $markers = $service->markers();

        $this->assertEquals(4, count($vessels));
        $this->assertEquals(1, $vessels[0]["properties"]["id"]);
        $this->assertEquals(2, $vessels[1]["properties"]["id"]);
        $this->assertEquals(3, $vessels[2]["properties"]["id"]);
        $this->assertEquals(4, $vessels[3]["properties"]["id"]);

        $identifiers = array();
        $identifiers[] = $markers[0]["properties"]["id"];
        $identifiers[] = $markers[1]["properties"]["id"];
        $identifiers[] = $markers[2]["properties"]["id"];

        $this->assertTrue(in_array(5, $identifiers));
        $this->assertTrue(in_array(6, $identifiers));
        $this->assertTrue(in_array(7, $identifiers));
    }

    public function testMarkerIdUniquenessRangeExceeded(): void
    {
        $service = new SeaChartServiceExt();
        $portCalls = array(
            ["imo" => 1],
            ["imo" => 2],
            ["imo" => 3],
            ["imo" => 4]
        );
        $service->fakeStateService->latestPortCallsData = $portCalls;
        $service->fakeFixedVesselRepository->returnFixedVessel = false;

        putenv("SEA_CHART_ID_RANGE_MAX=6");

        putenv("MAP_MARKERS=".file_get_contents(
            __DIR__ . "/sea-chart-data/test_data_sea_chart_markers.json"
        ));

        $vessels = $service->vessels();
        $markers = $service->markers();

        $this->assertEquals(4, count($vessels));
        $this->assertEquals(1, $vessels[0]["properties"]["id"]);
        $this->assertEquals(2, $vessels[1]["properties"]["id"]);
        $this->assertEquals(3, $vessels[2]["properties"]["id"]);
        $this->assertEquals(4, $vessels[3]["properties"]["id"]);

        $identifiers = array();
        $identifiers[] = $markers[0]["properties"]["id"];
        $identifiers[] = $markers[1]["properties"]["id"];
        $this->assertFalse(key_exists("id", $markers[2]["properties"]));
        $this->assertTrue(in_array(5, $identifiers));
        $this->assertTrue(in_array(6, $identifiers));
    }

    public function testUpdateVesselLocations(): void
    {
        $vesselLocations = array();
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "headingDegrees" => 172.4,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "imo" => 9552020,
            "mmsi" => 4363635
        ];
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocations($vesselLocations);

        $this->assertEquals(2, count($service->fakeSseService->triggeredEvents));

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals("sea-chart-markers", $service->fakeSseService->triggeredEvents[1]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[1]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[1]["data"]));

        $this->assertFalse(empty($res));
        $this->assertEquals(1, count($res["successful"]));
        $this->assertEquals(2, count($res["successful"][0]));
        $this->assertEquals(4363635, $res["successful"][0]["mmsi"]);
        $this->assertEquals(9552020, $res["successful"][0]["imo"]);
        $this->assertTrue(empty($res["failed"]));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            9552020
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            4363635
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            172.4
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->course_over_ground_degrees,
            null
        );
    }
    public function testUpdateVesselLocationsWithCogWithoutHeading(): void
    {
        $vesselLocations = array();
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "imo" => 9552020,
            "mmsi" => 4363635,
            "courseOverGroundDegrees" => 45.4,
        ];
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocations($vesselLocations);

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals("sea-chart-markers", $service->fakeSseService->triggeredEvents[1]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[1]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[1]["data"]));

        $this->assertFalse(empty($res));
        $this->assertEquals(1, count($res["successful"]));
        $this->assertEquals(2, count($res["successful"][0]));
        $this->assertEquals(4363635, $res["successful"][0]["mmsi"]);
        $this->assertEquals(9552020, $res["successful"][0]["imo"]);
        $this->assertTrue(empty($res["failed"]));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            9552020
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            4363635
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            null
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->course_over_ground_degrees,
            45.4
        );
    }
    public function testUpdateVesselLocationsWithMissingCogAndHeading(): void
    {
        $vesselLocations = array();
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "imo" => 9552020,
            "mmsi" => 4363635
        ];
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocations($vesselLocations);

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals("sea-chart-markers", $service->fakeSseService->triggeredEvents[1]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[1]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[1]["data"]));

        $this->assertFalse(empty($res));
        $this->assertEquals(1, count($res["successful"]));
        $this->assertEquals(2, count($res["successful"][0]));
        $this->assertEquals(4363635, $res["successful"][0]["mmsi"]);
        $this->assertEquals(9552020, $res["successful"][0]["imo"]);
        $this->assertTrue(empty($res["failed"]));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            9552020
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            4363635
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            null
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->course_over_ground_degrees,
            null
        );
    }
    public function testUpdateVesselLocationsWithoutImo(): void
    {
        $vesselLocations = array();
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "headingDegrees" => 172.4,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "mmsi" => 4363635
        ];
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocations($vesselLocations);

        $this->assertEquals(2, count($service->fakeSseService->triggeredEvents));

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals("sea-chart-markers", $service->fakeSseService->triggeredEvents[1]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[1]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[1]["data"]));

        $this->assertFalse(empty($res));
        $this->assertEquals(1, count($res["successful"]));
        $this->assertEquals(2, count($res["successful"][0]));
        $this->assertEquals(4363635, $res["successful"][0]["mmsi"]);
        $this->assertTrue(empty($res["successful"][0]["imo"]));
        $this->assertTrue(empty($res["failed"]));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            null
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            4363635
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            172.4
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
    }
    public function testUpdateVesselLocationsWithFailure(): void
    {
        $vesselLocations = array();
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "headingDegrees" => 172.4,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "mmsi" => 4363635
        ];
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "headingDegrees" => 511.0,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "mmsi" => 4365654
        ];
        $vesselLocations[] = [];
        $vesselLocations[] = [
            "latitude" => 61.207779,
            "longitude" => 18.229229,
            "headingDegrees" => 172.4,
            "speedKnots" => 5.3,
            "locationTimestamp" => "2020-11-02T12:03:05+00:00",
            "imo" => 5674636
        ];

        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocations($vesselLocations);

        $this->assertEquals(2, count($service->fakeSseService->triggeredEvents));

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals("sea-chart-markers", $service->fakeSseService->triggeredEvents[1]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[1]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[1]["data"]));

        $this->assertFalse(empty($res));

        $this->assertEquals(2, count($res["successful"]));

        $this->assertEquals(2, count($res["successful"][0]));
        $this->assertTrue(empty($res["successful"][0]["imo"]));
        $this->assertEquals(4363635, $res["successful"][0]["mmsi"]);

        $this->assertEquals(2, count($res["successful"][1]));
        $this->assertTrue(empty($res["successful"][1]["mmsi"]));
        $this->assertEquals(5674636, $res["successful"][1]["imo"]);

        $this->assertEquals(2, count($res["failed"]));

        $this->assertEquals(3, count($res["failed"][0]));
        $this->assertTrue(empty($res["failed"][0]["imo"]));
        $this->assertEquals(4365654, $res["failed"][0]["mmsi"]);
        $this->assertEquals("Invalid heading: 511", $res["failed"][0]["reason"]);

        $this->assertEquals(3, count($res["failed"][1]));
        $this->assertTrue(empty($res["failed"][1]["imo"]));
        $this->assertTrue(empty($res["failed"][1]["mmsi"]));
        $this->assertEquals("IMO and MMSI missing", $res["failed"][1]["reason"]);
    }
    public function testUpdateVesselLocationWithImo(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocation(
            61.207779,
            18.229229,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            9552020,
            null
        );
        $this->assertFalse(empty($res));
        $this->assertTrue($res["result"] === "OK");

        $this->assertTrue(empty($service->fakeSseService->lastTriggerCategory));
        $this->assertTrue(empty($service->fakeSseService->lastTriggerEvent));
        $this->assertTrue(empty($service->fakeSseService->lastTriggerData));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            9552020
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            null
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            172.4
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
    }
    public function testUpdateVesselLocationWithMmsi(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->updateVesselLocation(
            61.207779,
            18.229229,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            null,
            9552020
        );
        $this->assertFalse(empty($res));
        $this->assertTrue($res["result"] === "OK");

        $this->assertTrue(empty($service->fakeSseService->lastTriggerCategory));
        $this->assertTrue(empty($service->fakeSseService->lastTriggerEvent));
        $this->assertTrue(empty($service->fakeSseService->lastTriggerData));

        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->mmsi,
            9552020
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->imo,
            null
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->vessel_name,
            "Fake"
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->latitude,
            61.207779
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->longitude,
            18.229229
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->heading_degrees,
            172.4
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->speed_knots,
            5.3
        );
        $this->assertEquals(
            $service->fakeVesselLocationRepository->lastSavedModel->location_timestamp,
            "2020-11-02T12:03:05+00:00"
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage IMO and MMSI missing
     */
    public function testUpdateVesselLocationWithMissingIdentifiers(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            61.207779,
            18.229229,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            null,
            null
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Given location timestamp not in ISO format: 2020-11-02W12:03:05Y00Z00
     */
    public function testUpdateVesselLocationWithInvalidTimestamp(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            61.207779,
            18.229229,
            5.3,
            172.4,
            154.5,
            "2020-11-02W12:03:05Y00Z00",
            334355,
            null,
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Mandatory coordinates missing
     */
    public function testUpdateVesselLocationWithMissingCoordinates(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            null,
            null,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            45354,
            null,
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid latitude: -999.9
     */
    public function testUpdateVesselLocationWithInvalidLatitude(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            -999.9,
            17.0,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            45354,
            54354
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid longitude: -999.9
     */
    public function testUpdateVesselLocationWithInvalidLongitude(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            60.9,
            -999.9,
            5.3,
            172.4,
            154.5,
            "2020-11-02T12:03:05+00:00",
            45354,
            54354
        );
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid heading: 450.3
     */
    public function testUpdateVesselLocationWithInvalidHeading(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateVesselLocation(
            60.9,
            17.9,
            5.3,
            450.3,
            154.5,
            "2020-11-02T12:03:05+00:00",
            45354,
            54354
        );
    }
    public function testAddFixedVesselWithMmsi(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'M/S Shipsalot',
            null,
            888888
        );
        $this->assertEquals("OK", $res["result"]);

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals(null, $service->fakeFixedVesselRepository->lastSavedFixedVessel->imo);
        $this->assertEquals(888888, $service->fakeFixedVesselRepository->lastSavedFixedVessel->mmsi);
        $this->assertEquals('M/S Shipsalot', $service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_name);
        $this->assertEquals(2, $service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_type);
    }
    public function testAddFixedVesselWithImo(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'M/S Shipsalot',
            888888
        );
        $this->assertEquals("OK", $res["result"]);

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertEquals(888888, $service->fakeFixedVesselRepository->lastSavedFixedVessel->imo);
        $this->assertEquals(null, $service->fakeFixedVesselRepository->lastSavedFixedVessel->mmsi);
        $this->assertEquals('M/S Shipsalot', $service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_name);
        $this->assertEquals(2, $service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_type);
    }
    public function testAddFixedVesselInvalidImoFailure(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'Fake',
            999999,
            null
        );
        $this->assertEquals("ERROR", $res["result"]);
        $this->assertEquals("Invalid IMO", $res["message"]);

        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testAddFixedVesselMmsiBelongsToOtherVesselFailure(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'Fake',
            null,
            999999
        );
        $this->assertEquals("ERROR", $res["result"]);
        $this->assertEquals("MMSI belongs to another vessel", $res["message"]);

        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testAddFixedVesselImoBelongsToOtherVesselFailure(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'Fake',
            2,
            null
        );
        $this->assertEquals("ERROR", $res["result"]);
        $this->assertEquals("IMO belongs to another vessel", $res["message"]);

        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testAddFixedVesselInvalidImo(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'Fake',
            999999,
            null
        );
        $this->assertEquals("ERROR", $res["result"]);
        $this->assertEquals("Invalid IMO", $res["message"]);

        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testAddFixedVesselImoAndMmsiMissing(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->addFixedVessel(
            2,
            'Fake',
            null,
            null
        );
        $this->assertEquals("ERROR", $res["result"]);
        $this->assertEquals("IMO and MMSI missing", $res["message"]);
    }
    public function testDeleteFixedVessel(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->deleteFixedVessel(1);
        $this->assertEquals("OK", $res["result"]);

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testDeleteFixedVesselFailure(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->deleteFixedVessel(999999);
        $this->assertEquals("ERROR", $res["result"]);

        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));
    }
    public function testFixedVessels(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->fixedVessels(10, 1, "vessel_name");
        $this->assertTrue(key_exists("data", $res));
        $this->assertTrue(key_exists("test-key", $res["data"]));
        $this->assertEquals("test-data", $res["data"]["test-key"]);
        $this->assertTrue(key_exists("pagination", $res));
        $this->assertTrue(key_exists("start", $res["pagination"][0]));
        $this->assertEquals(1, $res["pagination"][0]["start"]);
        $this->assertTrue(key_exists("limit", $res["pagination"][0]));
        $this->assertEquals(10, $res["pagination"][0]["limit"]);
        $this->assertTrue(key_exists("total", $res["pagination"][0]));
        $this->assertEquals(100, $res["pagination"][0]["total"]);
    }
    public function testFixedVesselsWithSearch(): void
    {
        $service = new SeaChartServiceExt();
        $res = $service->fixedVessels(10, 1, "vessel_name", "Boaty Mc");
        $this->assertTrue(key_exists("data", $res));
        $this->assertTrue(key_exists("test-key", $res["data"]));
        $this->assertEquals("test-data", $res["data"]["test-key"]);
        $this->assertTrue(key_exists("pagination", $res));
        $this->assertTrue(key_exists("start", $res["pagination"][0]));
        $this->assertEquals(1, $res["pagination"][0]["start"]);
        $this->assertTrue(key_exists("limit", $res["pagination"][0]));
        $this->assertEquals(10, $res["pagination"][0]["limit"]);
        $this->assertTrue(key_exists("total", $res["pagination"][0]));
        $this->assertEquals(100, $res["pagination"][0]["total"]);
    }
    public function testFixedVessel(): void
    {
        $service = new SeaChartServiceExt();
        $fixedVessel = $service->fixedVessel(10);
        $this->assertTrue(isset($fixedVessel));
        $this->assertEquals(222222, $fixedVessel->imo);
        $this->assertEquals(222222, $fixedVessel->mmsi);
        $this->assertEquals('Fake', $fixedVessel->vessel_name);
        $this->assertEquals(10, $fixedVessel->vessel_type);
    }
    public function testUpdateFixedVessel(): void
    {
        $service = new SeaChartServiceExt();
        $result = $service->updateFixedVessel(1, 222222, 222222, 1, 'New Fake Name');

        $this->assertEquals(1, count($service->fakeSseService->triggeredEvents));

        $this->assertEquals("sea-chart-vessels", $service->fakeSseService->triggeredEvents[0]["category"]);
        $this->assertEquals("changed", $service->fakeSseService->triggeredEvents[0]["event"]);
        $this->assertTrue(empty($service->fakeSseService->triggeredEvents[0]["data"]));

        $this->assertTrue(key_exists("result", $result));
        $this->assertEquals("OK", $result["result"]);

        $this->assertEquals($service->fakeFixedVesselRepository->lastSavedFixedVessel->id, 1);
        $this->assertEquals($service->fakeFixedVesselRepository->lastSavedFixedVessel->imo, 222222);
        $this->assertEquals($service->fakeFixedVesselRepository->lastSavedFixedVessel->mmsi, 222222);
        $this->assertEquals($service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_type, 1);
        $this->assertEquals($service->fakeFixedVesselRepository->lastSavedFixedVessel->vessel_name, 'New Fake Name');
    }
    public function testUpdateFixedVesselImoBelongsToOtherVessel(): void
    {
        $service = new SeaChartServiceExt();
        $result = $service->updateFixedVessel(99, 222222, null, 1, 'New Fake Name');

        $this->assertTrue(key_exists("result", $result));
        $this->assertEquals("ERROR", $result["result"]);
        $this->assertEquals("IMO belongs to another vessel", $result["message"]);
    }
    public function testUpdateFixedVesselMmsiBelongsToOtherVessel(): void
    {
        $service = new SeaChartServiceExt();
        $result = $service->updateFixedVessel(99, null, 222222, 1, 'New Fake Name');

        $this->assertTrue(key_exists("result", $result));
        $this->assertEquals("ERROR", $result["result"]);
        $this->assertEquals("MMSI belongs to another vessel", $result["message"]);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Vessel does not exist
     */
    public function testUpdateFixedVesselWithBadId(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateFixedVessel(null, 222222, 222222, 1, 'New Fake Name');
    }
    public function testUpdateFixedVesselWithMissingIdentifiers(): void
    {
        $service = new SeaChartServiceExt();
        $result = $service->updateFixedVessel(1, null, null, 1, 'New Fake Name');

        $this->assertTrue(key_exists("result", $result));
        $this->assertEquals("ERROR", $result["result"]);
        $this->assertEquals("IMO and MMSI missing", $result["message"]);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Vessel does not exist
     */
    public function testUpdateFixedVesselWithInvalidId(): void
    {
        $service = new SeaChartServiceExt();
        $service->updateFixedVessel(99999999, 222222, 222222, 1, 'New Fake Name');
    }
}
