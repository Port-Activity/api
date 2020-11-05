<?php
namespace SMA\PAA\ORM;

use Exception;

class TimestampTimeTypeRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getTimeTypeMappings(): Array
    {
        $ret = array();
        $results = $this->getMultipleWithQuery("SELECT * FROM {$this->table}");

        foreach ($results as $result) {
            $ret[$result->name] = $result->id;
        }

        return $ret;
    }
    public function mapToId($timeType): ?int
    {
        $map = $this->getTimeTypeMappings();
        if (array_key_exists($timeType, $map)) {
            return $map[$timeType];
        }
        throw new Exception("Invalid time type: " . $timeType);
    }
}
