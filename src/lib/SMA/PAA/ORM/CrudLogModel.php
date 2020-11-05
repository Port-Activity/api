<?php
namespace SMA\PAA\ORM;

class CrudLogModel extends OrmModel
{
    public $table_name;
    public $data;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
