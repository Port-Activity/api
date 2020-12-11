<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;
use SMA\PAA\ORM\SeaChartMarkerTypeModel;

class SeaChartMarkerTypeRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function markerNameById(int $typeId): string
    {
        $model = $this->first(["id" => $typeId]);
        return isset($model) ? $model->name : null;
    }
}
