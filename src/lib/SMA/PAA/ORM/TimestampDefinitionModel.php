<?php
namespace SMA\PAA\ORM;

class TimestampDefinitionModel extends OrmModel
{
    public $name;
    public $time_type_id;
    public $state_id;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $name,
        int $timeTypeId,
        int $stateId
    ) {
        $this->name = $name;
        $this->time_type_id = $timeTypeId;
        $this->state_id = $stateId;
    }
}
