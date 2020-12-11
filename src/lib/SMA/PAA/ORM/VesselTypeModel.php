<?php
namespace SMA\PAA\ORM;

class VesselTypeModel extends OrmModel
{
    public $id;
    public $name;
    public $sea_chart_marker_type_id;
    public $created_at;
    public $created_by;
    public $modified_at;
    public $modified_by;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $id,
        string $name,
        int $seaChartMarkerTypeId,
        int $createdAt,
        int $createdBy,
        int $modifiedAt,
        int $modifiedBy
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->sea_chart_marker_type_id = $seaChartMarkerTypeId;
        $this->created_at = $createdAt;
        $this->created_by = $createdBy;
        $this->modified_at = $modifiedAt;
        $this->modified_by = $modifiedBy;
    }
}
