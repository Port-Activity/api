<?php
namespace SMA\PAA\ORM;

class PinnedVesselModel extends OrmModel
{
    public $user_id;
    public $vessel_ids;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $user_id,
        array $vessel_ids
    ) {
        $this->user_id = $user_id;
        $this->vessel_ids = json_encode($vessel_ids);
    }
}
