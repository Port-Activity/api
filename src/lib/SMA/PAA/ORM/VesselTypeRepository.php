<?php
namespace SMA\PAA\ORM;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\VesselTypeModel;

class VesselTypeRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false): int
    {
        if (empty($model->name) || $model->sea_chart_marker_type_id === 0) {
            throw new InvalidParameterException("Mandatory vessel type properties missing");
        }

        return parent::save($model, $skipCrudLog);
    }

    public function getMarkerType(int $id, int $default = 1): int
    {
        $entry = $this->first(["id" => $id]);
        return isset($entry) && isset($entry->sea_chart_marker_type_id)
            ? $entry->sea_chart_marker_type_id : $default;
    }

    public function getVesselTypeName(int $id): string
    {
        $entry = $this->first(["id" => $id]);
        return isset($entry) && isset($entry->name)
            ? $entry->name : "";
    }
}
