<?php
namespace SMA\PAA\ORM;

class PinnedVesselRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
