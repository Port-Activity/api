<?php
namespace SMA\PAA\ORM;

class TimestampModel extends OrmModel
{
    public $imo;
    public $vessel_name;
    public $time_type_id;
    public $state_id;
    public $time;
    public $payload;
    public $port_call_id;
    public $is_trash = "f";
    public $weight = 0;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    public function setIsTrash($value)
    {
        $this->is_trash = $value ? "t" : "f";
    }
    public function getIsTrash():bool
    {
        if ($this->is_trash === "t" || $this->is_trash === true) {
            return true;
        }

        return false;
    }
    public function set(
        int $imo,
        string $vesselName,
        string $timeType,
        string $state,
        string $time,
        array $payload
    ) {
        $stateRepository = new TimestampStateRepository();
        $states = $stateRepository->getStateMappings();

        $timeTypeRepository = new TimestampTimeTypeRepository();
        $timeTypes = $timeTypeRepository->getTimeTypeMappings();

        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->time_type_id = $timeTypes[$timeType];
        $this->state_id = $states[$state];
        $this->time = $time;
        $this->payload = json_encode($payload);
    }
}
