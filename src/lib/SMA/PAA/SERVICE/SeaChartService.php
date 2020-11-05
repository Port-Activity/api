<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\SERVICE\StateService;
use SMA\PAA\SERVICE\PortCallService;

class SeaChartService
{
    private $stateService;
    private $portCallService;

    public function __construct()
    {
        $this->setStateService(new StateService());
        $this->setPortCallService(new PortCallService());
    }

    protected function setStateService($stateServ)
    {
        $this->stateService = $stateServ;
    }

    protected function setPortCallService($portCallServ)
    {
        $this->portCallService = $portCallServ;
    }

    public function updateVesselLocations(
        array $vesselLocations
    ): array {

        // TODO: - to be called by agent responsible for updating vessel (incl. timeline vessels
        //         and other specified port actors) location information
        //       - add route to here (with suitable permission if needed) e.g.
        //         ,"POST:/sea-chart/vessels"     => "update_vessel_location:SeaChart:updateVesselLocations"
        //       - store information in database
        //       - trigger update for client
        //
        // Format in simplest form could be array of:
        //
        // [
        //    "imo": "9552020",
        //    "mmsi": "255805989",
        //    "latitude": "61.207779",
        //    "longitude: "18.229229",
        //    "heading": "172.4",
        //    "speed_knots": "5.3"
        // ]
        //
        // Possibly MMSI and vessel name or vessel id as well.

        return ["result" => "OK"];
    }

    public function vessels(): array
    {
        $features = array();

        $stateService = $this->stateService;

        $res = $stateService->getSet(StateService::LATEST_PORT_CALLS, function () use ($stateService) {
            return $this->portCallService->portCallsOngoing();
        });

        $portCalls = key_exists("portcalls", $res) ? $res["portcalls"] : [];

        if (!empty($portCalls)) {
            foreach ($portCalls as $portCall) {
                $ship = $portCall["ship"];
                if (!empty($ship) && !empty($ship["imo"])) {
                    $vesselName = empty($ship["vessel_name"]) ? "" : $ship["vessel_name"];
                    $vesselImo = empty($ship["imo"]) ? "" : $ship["imo"];
                    $locationData = $this->vesselData($vesselImo, $portCall);
                    if (!empty($locationData)) {
                        $features[] = array(
                            "type" => "Feature",
                            "geometry" => array("type" => "Point",
                                "coordinates" => array($locationData["longitude"], $locationData["latitude"])),
                            "properties" => array("name" => $vesselName,
                                "mmsi" => $locationData["mmsi"],
                                "imo" => $vesselImo,
                                "latitude" => $locationData["latitude"],
                                "longitude" => $locationData["longitude"],
                                "speed_knots" => $locationData["speed_knots"],
                                "heading_degrees" => $locationData["heading_degrees"],
                                "marker_class" => "timeline",
                            )
                        );
                    }
                }
            }
        }

        // TODO: fill other port actors

        return $features;
    }
    public function markers(): array
    {
        // TODO: Return marker data as GeoJSON
        return array();
    }
    private function vesselData($imo, $portCall): array
    {
        // Temporary implementation for getting MMSI from timestamps.
        // Will be replaced with actual data once agent provided data
        // is available.

        $mmsi = "";
        if (!empty($portCall["portcalls"][0]["events"])) {
            $shipTimestampEvents = $portCall["portcalls"][0]["events"];
            foreach ($shipTimestampEvents as $event) {
                if (!empty($event["timestamps"])) {
                    foreach ($event["timestamps"] as $timestamp) {
                        if (!empty($timestamp["payload"]["mmsi"])) {
                            $mmsi = $timestamp["payload"]["mmsi"];
                            break;
                        }
                    }
                }
            }
        }

        // Temporary implementation for getting variable location information.
        // Will be replaced with actual data once agent provided data is available.

        $latitudeSeed = 57.998247;
        $appliedLatitude = $latitudeSeed + (mt_rand() / mt_getrandmax());

        $longitudeSeed = 20.401054;
        $appliedLongitude = $longitudeSeed + (mt_rand() / mt_getrandmax());

        $headingDegrees = "356.4";
        $speedKnots = "6.5";

        return array(
            "mmsi" => $mmsi,
            "latitude" => $appliedLatitude,
            "longitude" => $appliedLongitude,
            "heading_degrees" => $headingDegrees,
            "speed_knots" => $speedKnots
        );
    }
}
