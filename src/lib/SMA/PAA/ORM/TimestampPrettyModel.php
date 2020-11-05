<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\TimestampTimeTypeRepository;
use SMA\PAA\ORM\TimestampStateRepository;

class TimestampPrettyModel extends TimestampModel
{
    public $time_type;
    public $state;

    public static function timeTypeId(string $timeTypeName, TimestampTimeTypeRepository $repository = null): ?int
    {
        if ($repository === null) {
            $repository = new TimestampTimeTypeRepository();
        }

        return $repository->mapToId($timeTypeName);
    }

    public static function stateId(string $timeTypeName, TimestampStateRepository $repository = null): ?int
    {
        if ($repository === null) {
            $repository = new TimestampStateRepository();
        }

        return $repository->mapToId($timeTypeName);
    }

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    private function cache($key, $callback)
    {
        if (!isset($GLOBALS[__CLASS__][$key])) {
            $GLOBALS[__CLASS__][$key] = call_user_func($callback);
        }
        return $GLOBALS[__CLASS__][$key];
    }
    private function timeTypes()
    {
        return $this->cache(__METHOD__, function () {
            $timeTypeRepository = new TimestampTimeTypeRepository();
            return $timeTypeRepository->getTimeTypeMappings();
        });
    }

    private function states()
    {
        return $this->cache(__METHOD__, function () {
            $stateRepository = new TimestampStateRepository();
            return $stateRepository->getStateMappings();
        });
    }
    public function setFromTimestamp(TimestampModel $timestampModel)
    {
        $timeTypes = $this->timeTypes();
        $states = $this->states();

        $this->id = $timestampModel->id;
        $this->port_call_id = $timestampModel->port_call_id;
        $this->imo = $timestampModel->imo;
        $this->vessel_name = $timestampModel->vessel_name;
        $this->time_type_id = $timestampModel->time_type_id;
        $this->state_id = $timestampModel->state_id;
        $this->time = $timestampModel->time;
        $this->payload = $timestampModel->payload;
        $this->is_trash = $timestampModel->is_trash;
        $this->weight = $timestampModel->weight;
        $this->created_by = $timestampModel->created_by;
        $this->created_at = $timestampModel->created_at;
        $this->modified_by = $timestampModel->modified_by;
        $this->modified_at = $timestampModel->modified_at;

        $this->time_type = array_search($this->time_type_id, $timeTypes);
        $this->state = array_search($this->state_id, $states);
    }
}
