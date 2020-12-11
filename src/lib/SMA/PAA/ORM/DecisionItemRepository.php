<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\DecisionItemModel;

class DecisionItemRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
