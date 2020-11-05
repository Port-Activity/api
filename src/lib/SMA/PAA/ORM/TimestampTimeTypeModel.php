<?php
namespace SMA\PAA\ORM;

class TimestampTimeTypeModel extends OrmModel
{
    public $name;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
