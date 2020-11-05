<?php
namespace SMA\PAA\ORM;

class NominationBerthModel extends OrmModel
{
    public $nomination_id;
    public $berth_id;
    public $berth_priority;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $nominationId,
        int $berthId,
        int $berthPriority
    ) {
        $this->nomination_id = $nominationId;
        $this->berth_id = $berthId;
        $this->berth_priority = $berthPriority;
    }
}
