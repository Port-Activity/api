<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\SeaChartVesselLocationRepository;
use SMA\PAA\ORM\SeaChartVesselLocationModel;
use SMA\PAA\ORM\SeaChartFixedVesselRepository;
use SMA\PAA\ORM\SeaChartFixedVesselModel;
use SMA\PAA\ORM\SeaChartMarkerTypeRepository;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\VesselRepository;
use SMA\PAA\ORM\VesselTypeRepository;

class SeaChartService
{
    private $fixedVesselRepository;
    private $locationRepository;
    private $markerTypeRepository;
    private $vesselRepository;
    private $sseService;
    private $stateService;
    private $vesselTypeRepository;

    public function __construct()
    {
        $this->setStateService(new StateService());
        $this->setFixedVesselRepository(new SeaChartFixedVesselRepository());
        $this->setVesselLocationRepository(new SeaChartVesselLocationRepository());
        $this->setMarkerTypeRepository(new SeaChartMarkerTypeRepository());
        $this->setVesselRepository(new VesselRepository());
        $this->setSseService(new SseService());
        $this->setVesselTypeRepository(new VesselTypeRepository());
    }
    protected function setFixedVesselRepository($fixedVesselRepository)
    {
        $this->fixedVesselRepository = $fixedVesselRepository;
    }
    protected function setVesselLocationRepository($locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }
    protected function setMarkerTypeRepository($markerTypeRepository)
    {
        $this->markerTypeRepository = $markerTypeRepository;
    }
    protected function setVesselRepository($vesselRepository)
    {
        $this->vesselRepository = $vesselRepository;
    }
    public function setSseService($sseService)
    {
        $this->sseService = $sseService;
    }
    public function setStateService($stateService)
    {
        $this->stateService = $stateService;
    }
    public function setVesselTypeRepository($vesselTypeRepository)
    {
        $this->vesselTypeRepository = $vesselTypeRepository;
    }

    private function validateImo($imo)
    {
        $this->vesselRepository->getWithImo($imo); // throws error for invalid
    }

    private function validateMmsi($mmsi)
    {
        $details = $this->stateService->get(StateService::LATEST_PORT_CALL_DETAILS);
        if ($details && is_array($details)) {
            foreach ($details as $detail) {
                if (key_exists("mmsi", $detail) && $detail["mmsi"] === $mmsi) {
                    // found, ok
                    return;
                }
            }
        }
        $fixedVessel = $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi);
        if (!isset($fixedVessel)) {
            throw new InvalidParameterException("MMSI can not be matched to fixed vessels");
        }
    }

    private function imoFromPortCalls($mmsi)
    {
        if (!empty($mmsi)) {
            $details = $this->stateService->get(StateService::LATEST_PORT_CALL_DETAILS);
            if ($details && is_array($details)) {
                foreach ($details as $detail) {
                    if (key_exists("mmsi", $detail)
                        && $detail["mmsi"] === $mmsi
                        && key_exists("imo", $detail)
                        && !empty($detail["imo"])) {
                        return $detail["imo"];
                    }
                }
            }
        }
        return null;
    }

    private function validateInputs(
        $imo,
        $mmsi,
        $latitude,
        $longitude,
        $headingDegrees,
        $speedKnots,
        $locationTimestamp,
        $courseOverGroundDegrees
    ) {
        if (empty($imo) && empty($mmsi)) {
            throw new InvalidParameterException("IMO and MMSI missing");
        }

        if (!empty($imo)) {
            $this->validateImo($imo);
        } else {
            $this->validateMmsi($mmsi);
        }

        if (empty($latitude) || empty($longitude)) {
            throw new InvalidParameterException("Mandatory coordinates missing");
        }

        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidParameterException("Invalid latitude: " . $latitude);
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidParameterException("Invalid longitude: " . $longitude);
        }

        if ($headingDegrees) {
            if ($headingDegrees < 0.0 || $headingDegrees > 360.0) {
                throw new InvalidParameterException("Invalid heading: " . $headingDegrees);
            }
        }

        if ($courseOverGroundDegrees) {
            if ($courseOverGroundDegrees < 0.0 || $courseOverGroundDegrees > 360.0) {
                throw new InvalidParameterException("Invalid course over ground: " . $courseOverGroundDegrees);
            }
        }

        $dateTools = new DateTools();
        if (!$dateTools->isValidIsoDateTime($locationTimestamp)) {
            throw new InvalidParameterException("Given location timestamp not in ISO format: " . $locationTimestamp);
        }
    }
    private function resolveVesselName($imo, $mmsi) : string
    {
        // Try fixed vessels first, this way those can have customized
        // names if needed.
        $fixedVesselCandidate = !empty($imo)
            ? $this->fixedVesselRepository->getFixedVesselByImo($imo)
            : $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi);
        if (isset($fixedVesselCandidate)) {
            return $fixedVesselCandidate->vessel_name;
        }

        if (!empty($imo)) {
            return $this->vesselRepository->getVesselName($imo);
        }
        return "";
    }

    private function resolveVesselMarkerType($imo, $mmsi) : int
    {
        $fixedVesselCandidate = !empty($imo)
            ? $this->fixedVesselRepository->getFixedVesselByImo($imo)
            : $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi);
        if (isset($fixedVesselCandidate)
            && isset($fixedVesselCandidate->vessel_type)
            && $fixedVesselCandidate->vessel_type !== 1) {
            return $this->vesselTypeRepository->getMarkerType($fixedVesselCandidate->vessel_type, 1);
        }
        if ($imo) {
            try {
                $vessel = $this->vesselRepository->getWithImo($imo);
                if (isset($vessel) && isset($vessel->vessel_type)) {
                    return $this->vesselTypeRepository->getMarkerType($vessel->vessel_type, 1);
                }
            } catch (\Exception $e) {
            }
        }

        return 1;
    }

    private function resolveVesselTypeName($imo, $mmsi) : string
    {
        $fixedVesselCandidate = !empty($imo)
            ? $this->fixedVesselRepository->getFixedVesselByImo($imo)
            : !empty($mmsi)
                ? $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi)
                : null;

        if (isset($fixedVesselCandidate)
            && isset($fixedVesselCandidate->vessel_type)
            && $fixedVesselCandidate->vessel_type !== 1) {
            return $this->vesselTypeRepository->getVesselTypeName($fixedVesselCandidate->vessel_type);
        }

        if ($imo) {
            try {
                $vessel = $this->vesselRepository->getWithImo($imo);
                return isset($vessel) && isset($vessel->vessel_type)
                    ? $this->vesselTypeRepository->getVesselTypeName($vessel->vessel_type)
                    : "Uknown Vessel Type";
            } catch (\Exception $e) {
            }
        }
        return "Uknown Vessel Type";
    }

    private function buildVesselsAndMarkers(): array
    {
        $vesselsAndMarkers = array();

        $vessels = array();

        $addedFixedVesselImos = array();
        $fixedVessels = $this->fixedVesselRepository->getFixedVessels();
        if (!empty($fixedVessels)) {
            foreach ($fixedVessels as $fixedVessel) {
                if (isset($fixedVessel->imo)) {
                    $addedFixedVesselImos[] = $fixedVessel->imo;
                    $this->addVesselEntry(
                        $this->locationRepository->getLocationByImo($fixedVessel->imo),
                        $vessels,
                        $fixedVessel
                    );
                } elseif (isset($fixedVessel->mmsi)) {
                    $this->addVesselEntry(
                        $this->locationRepository->getLocationByMmsi($fixedVessel->mmsi),
                        $vessels,
                        $fixedVessel
                    );
                }
            }
        }

        $timelineImos = array();
        $details = $this->stateService->get(StateService::LATEST_PORT_CALL_DETAILS);
        if ($details && is_array($details)) {
            foreach ($details as $detail) {
                if (key_exists("imo", $detail)
                    && !empty($detail["imo"])
                    && !in_array($detail["imo"], $timelineImos)
                    && !in_array($detail["imo"], $addedFixedVesselImos)) {
                    $timelineImos[] = $detail["imo"];
                }
            }
        }

        $timelineVesselLocations = $this->locationRepository->getLocationsByImos($timelineImos);
        foreach ($timelineVesselLocations as $timelineVesselLocation) {
            $this->addVesselEntry($timelineVesselLocation, $vessels);
        }

        $vesselsAndMarkers["vessels"] = $vessels;

        $markers = array();

        $content = getenv("MAP_MARKERS");
        if (!empty($content)) {
            $markers = json_decode($content, true);
        }

        $usedIdentifiers = [];

        foreach ($vessels as $vessel) {
            if (key_exists("properties", $vessel)
                && key_exists("id", $vessel["properties"])
                && !empty($vessel["properties"]["id"])) {
                $usedIdentifiers[] = $vessel["properties"]["id"];
            }
        }

        $rangeMax = empty(getenv("SEA_CHART_ID_RANGE_MAX"))
            ? getrandmax()
            : getenv("SEA_CHART_ID_RANGE_MAX");

        foreach ($markers as $key => $marker) {
            if (key_exists("properties", $marker)) {
                do {
                    if (count($usedIdentifiers) >= $rangeMax) {
                        unset($uniqieIdentifier);
                        break;
                    }
                    $uniqieIdentifier = rand(1, $rangeMax);
                } while (in_array($uniqieIdentifier, $usedIdentifiers));

                if (isset($uniqieIdentifier)) {
                    $markers[$key]["properties"]["id"] = $uniqieIdentifier;
                    $usedIdentifiers[] = $uniqieIdentifier;
                }
            }
        }

        $vesselsAndMarkers["markers"] = $markers;

        return $vesselsAndMarkers;
    }

    private function doUpdateVesselLocation(
        bool $rebuildCachedData,
        $latitude,
        $longitude,
        $speedKnots,
        $headingDegrees,
        $courseOverGroundDegrees,
        $locationTimestamp,
        $imo = null,
        $mmsi = null
    ): array {
        // If IMO is not provided, try to resolve through
        // port calls and fallback to null as last option.
        $appliedImo = ($imo !== null)
            ? $imo
            : $this->imoFromPortCalls($mmsi);

        $this->validateInputs(
            $imo,
            $mmsi,
            $latitude,
            $longitude,
            $headingDegrees,
            $speedKnots,
            $locationTimestamp,
            $courseOverGroundDegrees
        );

        $modelCandidate = new SeaChartVesselLocationModel();
        $modelCandidate->imo = $appliedImo;
        $modelCandidate->mmsi = $mmsi;
        $modelCandidate->latitude = $latitude;
        $modelCandidate->longitude = $longitude;
        $modelCandidate->speed_knots = $speedKnots;
        $modelCandidate->location_timestamp = $locationTimestamp;
        $modelCandidate->vessel_name = $this->resolveVesselName($appliedImo, $mmsi);
        $modelCandidate->modified_by = 1;
        $modelCandidate->created_by = 1;
        $modelCandidate->heading_degrees = $headingDegrees;
        $modelCandidate->course_over_ground_degrees = $courseOverGroundDegrees;

        if ($this->locationRepository->save($modelCandidate)) {
            if ($rebuildCachedData) {
                $stateService = $this->stateService;
                $stateService->set(
                    StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
                    $this->buildVesselsAndMarkers(),
                    60
                );
            }
            return ["result" => "OK"];
        } else {
            return ["result" => "ERROR"];
        }
    }

    public function updateVesselLocations(
        array $locations
    ): array {
        $successful = array();
        $failed = array();

        foreach ($locations as $location) {
            try {
                $this->doUpdateVesselLocation(
                    false,
                    key_exists("latitude", $location) ? $location["latitude"] : null,
                    key_exists("longitude", $location) ? $location["longitude"] : null,
                    key_exists("speedKnots", $location) ? $location["speedKnots"] : null,
                    key_exists("headingDegrees", $location) ? $location["headingDegrees"] : null,
                    key_exists("courseOverGroundDegrees", $location) ? $location["courseOverGroundDegrees"] : null,
                    key_exists("locationTimestamp", $location) ? $location["locationTimestamp"] : null,
                    key_exists("imo", $location) ? $location["imo"] : null,
                    key_exists("mmsi", $location) ? $location["mmsi"] : null
                );
                $successful[] = [
                    "imo" => key_exists("imo", $location) ? $location["imo"] : null,
                    "mmsi" => key_exists("mmsi", $location) ? $location["mmsi"] : null
                ];
            } catch (\Exception $e) {
                $failed[] = [
                    "imo" => key_exists("imo", $location) ? $location["imo"] : null,
                    "mmsi" => key_exists("mmsi", $location) ? $location["mmsi"] : null,
                    "reason" => $e->getMessage()
                ];
            }
        }

        $stateService = $this->stateService;

        $stateService->set(
            StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
            $this->buildVesselsAndMarkers(),
            60
        );

        // Both were rebuilt (internal identifiers possibly changed)
        $this->sseService->trigger("sea-chart-vessels", "changed", []);
        $this->sseService->trigger("sea-chart-markers", "changed", []);

        $results = array();
        $results["successful"] = $successful;
        $results["failed"] = $failed;
        return $results;
    }

    public function updateVesselLocation(
        $latitude,
        $longitude,
        $speedKnots,
        $headingDegrees,
        $courseOverGroundDegrees,
        $locationTimestamp,
        $imo = null,
        $mmsi = null
    ): array {
        return $this->doUpdateVesselLocation(
            true,
            $latitude,
            $longitude,
            $speedKnots,
            $headingDegrees,
            $courseOverGroundDegrees,
            $locationTimestamp,
            $imo,
            $mmsi
        );
    }
    public function vessels(): array
    {
        $stateService = $this->stateService;
        $latestVesselsAndMarkers = $stateService->getSet(
            StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
            function () {
                return $this->buildVesselsAndMarkers();
            },
            60
        );
        return key_exists("vessels", $latestVesselsAndMarkers)
            && isset($latestVesselsAndMarkers["vessels"])
            ? $latestVesselsAndMarkers["vessels"]
            : [];
    }
    public function markers(): array
    {
        $stateService = $this->stateService;
        $latestVesselsAndMarkers = $stateService->getSet(
            StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
            function () {
                return $this->buildVesselsAndMarkers();
            },
            60
        );
        return key_exists("markers", $latestVesselsAndMarkers)
            && isset($latestVesselsAndMarkers["markers"])
            ? $latestVesselsAndMarkers["markers"]
            : [];
    }
    public function addFixedVessel(
        int $vesselType,
        string $vesselName,
        $imo = null,
        $mmsi = null
    ): array {
        if ($imo) {
            try {
                $this->validateImo(intval($imo));
            } catch (\Exception $e) {
                return ["result" => "ERROR", "message" => "Invalid IMO"];
            }
        }

        if (empty($imo) && empty($mmsi)) {
            return ["result" => "ERROR", "message" => "IMO and MMSI missing"];
        }

        $existingImoEntry = empty($imo) ? null : $this->fixedVesselRepository->getFixedVesselByImo($imo);
        if ($existingImoEntry) {
            return ["result" => "ERROR", "message" => "IMO belongs to another vessel"];
        }

        $existingMmsiEntry = empty($mmsi) ? null : $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi);
        if ($existingMmsiEntry) {
            return ["result" => "ERROR", "message" => "MMSI belongs to another vessel"];
        }

        $fixedVesselModel = new SeaChartFixedVesselModel();
        $fixedVesselModel->imo = $imo ? intVal($imo) : null;
        $fixedVesselModel->mmsi = $mmsi ? intVal($mmsi) : null;
        $fixedVesselModel->vessel_type = $vesselType ? $vesselType : 1;
        $fixedVesselModel->vessel_name = $vesselName;

        if ($this->fixedVesselRepository->save($fixedVesselModel)) {
            $stateService = $this->stateService;
            $stateService->set(
                StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
                $this->buildVesselsAndMarkers()
            );
            $this->sseService->trigger("sea-chart-vessels", "changed", []);
            return ["result" => "OK"];
        } else {
            return ["result" => "ERROR"];
        }
    }
    public function deleteFixedVessel(
        int $id
    ): array {
        $res = $this->fixedVesselRepository->delete([$id]);
        if (isset($res)) {
            $stateService = $this->stateService;
            $stateService->set(
                StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
                $this->buildVesselsAndMarkers()
            );
            $this->sseService->trigger("sea-chart-vessels", "changed", []);
            return ["result" => "OK"];
        } else {
            return ["result" => "ERROR"];
        }
    }
    public function fixedVessel(int $id): ?SeaChartFixedVesselModel
    {
        $query = ["public.sea_chart_fixed_vessel.id" => $id];

        $joins = [];
        $joins["VesselTypeRepository"] = [
            "values" => ["name" => "readable_type_name"],
            "join" => ["vessel_type" => "id"]
        ];

        $query["complex_select"] = $this->fixedVesselRepository->buildJoinSelect($joins);
        return $this->fixedVesselRepository->first($query);
    }
    public function fixedVessels(int $limit, int $offset, string $sort, string $search = '')
    {
        $query = [];

        $joins = [];
        $joins["VesselTypeRepository"] = [
            "values" => ["name" => "readable_type_name"],
            "join" => ["vessel_type" => "id"]
        ];

        $query["complex_select"] = $this->fixedVesselRepository->buildJoinSelect($joins);

        if (!empty($search)) {
            if (preg_match("/^\^/", $search)) {
                $query["vessel_name"] = ["ilike" => substr($search, 1) . "%"];
            } else {
                $query["vessel_name"] = ["ilike" => "%" . $search . "%"];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        return $this->fixedVesselRepository->listPaginated(
            $query,
            $offset,
            $limit,
            $sort
        );
    }
    public function updateFixedVessel($id, $imo, $mmsi, $vesselType, $vesselName)
    : array
    {
        if (!isset($id) || empty($id)) {
            throw new InvalidParameterException("Vessel does not exist");
        }

        $fixedVessel = $this->fixedVesselRepository->get($id);
        if (!isset($fixedVessel)) {
            throw new InvalidParameterException("Vessel does not exist");
        }

        if (empty($imo) && empty($mmsi)) {
            return ["result" => "ERROR", "message" => "IMO and MMSI missing"];
        }

        $existingImoEntry = empty($imo) ? null : $this->fixedVesselRepository->getFixedVesselByImo($imo);
        if (($existingImoEntry && $existingImoEntry->id != $id)) {
            return ["result" => "ERROR", "message" => "IMO belongs to another vessel"];
        }

        $existingMmsiEntry = empty($mmsi) ? null : $this->fixedVesselRepository->getFixedVesselByMmsi($mmsi);
        if (($existingMmsiEntry && $existingMmsiEntry->id != $id)) {
            return ["result" => "ERROR", "message" => "MMSI belongs to another vessel"];
        }

        $fixedVessel->imo = $imo ? intVal($imo) : null;
        $fixedVessel->mmsi = $mmsi ? intVal($mmsi) : null;
        $fixedVessel->vessel_type = $vesselType ? $vesselType : 1;
        $fixedVessel->vessel_name = $vesselName;

        try {
            if ($this->fixedVesselRepository->save($fixedVessel)) {
                $stateService = $this->stateService;
                $stateService->set(
                    StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
                    $this->buildVesselsAndMarkers(),
                    60
                );
                $this->sseService->trigger("sea-chart-vessels", "changed", []);
            }
            return ["result" => "OK"];
        } catch (\Exception $e) {
            return ["result" => "ERROR", "message" => "Invalid vessel properties"];
        }
    }
    private function addVesselEntry(
        ?SeaChartVesselLocationModel $locationModel,
        array &$targetArray,
        SeaChartFixedVesselModel $fixedVessel = null
    ) {
        if (isset($locationModel)) {
            $markerId = $this->resolveVesselMarkerType($locationModel->imo, $locationModel->mmsi);
            $markerName = $this->markerTypeRepository->markerNameById($markerId);
            $targetArray[] = array(
                "type" => "Feature",
                "geometry" => array("type" => "Point",
                    "coordinates" => array(
                        floatVal($locationModel->longitude),
                        floatVal($locationModel->latitude)
                    )
                ),
                "properties" => array(
                    "name" => $fixedVessel ? $fixedVessel->vessel_name : $locationModel->vessel_name,
                    "mmsi" => isset($locationModel->mmsi) ? intVal($locationModel->mmsi) : null,
                    "imo" => isset($locationModel->imo) ? intVal($locationModel->imo) : null,
                    "latitude" => floatVal($locationModel->latitude),
                    "longitude" => floatVal($locationModel->longitude),
                    "speed_knots" => isset($locationModel->speed_knots) ? floatVal($locationModel->speed_knots) : null,
                    "marker_class" => isset($markerName) ? $markerName : null,
                    "heading_degrees" => (isset($locationModel->heading_degrees)
                        ? floatVal($locationModel->heading_degrees) : null),
                    "course_over_ground_degrees" => (isset($locationModel->course_over_ground_degrees)
                        ? floatVal($locationModel->course_over_ground_degrees) : null),
                    "location_timestamp" => $locationModel->location_timestamp,
                    "id" => $locationModel->id,
                    "vessel_type_name" => $this->resolveVesselTypeName($locationModel->imo, $locationModel->mmsi)
                )
            );
        }
    }
}
