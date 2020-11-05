<?php
namespace SMA\PAA\ORM;

class CrudLogRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
