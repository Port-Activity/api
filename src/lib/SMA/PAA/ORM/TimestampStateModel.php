<?php
namespace SMA\PAA\ORM;

class TimestampStateModel extends OrmModel
{
    public $name;
    public $state_type_id;
    public $description;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
