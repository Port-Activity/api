<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\BerthModel;

class BerthRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
