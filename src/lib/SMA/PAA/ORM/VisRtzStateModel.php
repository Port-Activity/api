<?php
namespace SMA\PAA\ORM;

class VisRtzStateModel extends OrmModel
{
    // TODO: These constants should be fetched from database
    const CALCULATED_SCHEDULE_NOT_FOUND     = 1;
    const SYNC_WITH_ETA_FOUND               = 2;
    const SYNC_WITHOUT_ETA_FOUND            = 3;
    const SYNC_NOT_FOUND_CAN_BE_ADDED       = 4;
    const SYNC_NOT_FOUND_CAN_NOT_BE_ADDED   = 5;
    const RTA_SENT                          = 6;

    public $name;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
