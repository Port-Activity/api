<?php
namespace SMA\PAA\ORM;

class SeaChartMarkerTypeModel extends OrmModel
{
    public $name;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $name
    ) {
        $this->name = $name;
    }
}
