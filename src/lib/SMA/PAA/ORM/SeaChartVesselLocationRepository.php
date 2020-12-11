<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;
use SMA\PAA\ORM\SeaChartVesselLocationModel;
use DateTime;
use SMA\PAA\TOOL\DateTools;

class SeaChartVesselLocationRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if (($model->imo === 0 && $model->mmsi === 0)
            || $model->latitude === 0 || $model->longitude === 0) {
            throw new InvalidArgumentException(
                "Vessel identifier and/or location information missing"
            );
        }

        // First try with IMO if provided. In case DB entry with
        // matching MMSI but without IMO exists, look by MMSI as
        // fallback. This way adding IMO to existing MMSI based entry
        // succeeds (should e.g. fixed vessel without IMO gets one).
        $dbModel = $model->imo !== null
            ? $this->first(["imo" => $model->imo])
            : null;
        if (!isset($dbModel) && $model->mmsi !== null) {
            $dbModel = $this->first(["mmsi" => $model->mmsi]);
        }

        if (isset($dbModel)) {
            $hasChanges = ($dbModel->imo !== $model->imo
                || $dbModel->mmsi !== $model->mmsi
                || $dbModel->latitude !== $model->latitude
                || $dbModel->longitude !== $model->longitude
                || $dbModel->heading_degrees !== $model->heading_degrees
                || $dbModel->speed_knots !== $model->speed_knots
                || $dbModel->location_timestamp !== $model->location_timestamp
                || $dbModel->vessel_name !== $model->vessel_name
                || $dbModel->course_over_ground_degrees !== $model->course_over_ground_degrees);

            if ($hasChanges) {
                $dbModel->imo = $model->imo;
                $dbModel->mmsi = $model->mmsi;
                $dbModel->latitude = $model->latitude;
                $dbModel->longitude = $model->longitude;
                $dbModel->heading_degrees = $model->heading_degrees;
                $dbModel->speed_knots = $model->speed_knots;
                $dbModel->location_timestamp = $model->location_timestamp;
                $dbModel->vessel_name = $model->vessel_name;
                $dbModel->course_over_ground_degrees = $model->course_over_ground_degrees;
                return parent::save($dbModel, $skipCrudLog);
            } else {
                return $dbModel->id;
            }
        } else {
            return parent::save($model, $skipCrudLog);
        }
    }
    public function getLocationsByImos(array $imos)
    {
        $query['imo'] = ['in' => $imos];
        $this->addLocationTimestampAgeRestriction($query);
        return $this->list($query, 0, count($imos));
    }
    public function getLocationByImo(int $imo): ?SeaChartVesselLocationModel
    {
        $query["imo"] = $imo;
        $this->addLocationTimestampAgeRestriction($query);
        return $this->first($query);
    }
    public function getLocationByMmsi(int $mmsi): ?SeaChartVesselLocationModel
    {
        $query["mmsi"] = $mmsi;
        $this->addLocationTimestampAgeRestriction($query);
        return $this->first($query);
    }
    private function addLocationTimestampAgeRestriction(array &$query)
    {
        $vesselLocationMaxAgeMinutes = getenv("MAP_VESSEL_LOCATION_MAX_AGE_MINUTES");

        if (isset($vesselLocationMaxAgeMinutes) && is_numeric($vesselLocationMaxAgeMinutes)) {
            $dateTools = new DateTools();
            $earliestAllowedLocationTime = new DateTime(
                $dateTools->subIsoDuration($dateTools->now(), "PT" . intVal($vesselLocationMaxAgeMinutes) . "M")
            );
            $innerQuery["gte"] = $earliestAllowedLocationTime->format("Y-m-d\TH:i:sP");
            $query['location_timestamp'] = $innerQuery;
        }
    }
}
