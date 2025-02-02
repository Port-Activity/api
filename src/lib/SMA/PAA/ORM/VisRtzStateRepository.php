<?php
namespace SMA\PAA\ORM;

class VisRtzStateRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getStateMappings(): Array
    {
        $ret = array();
        $results = $this->getMultipleWithQuery("SELECT * FROM {$this->table}");

        foreach ($results as $result) {
            $ret[$result->name] = $result->id;
        }

        return $ret;
    }
    public function mapToId($state): ?int
    {
        $map = $this->getStateMappings();
        if (array_key_exists($state, $map)) {
            return $map[$state];
        }
        throw new \Exception("Invalid rtz state: " . $state);
    }
    public function getStateNameWithStateId(string $stateId): string
    {
        $model = $this->first(["id" => $stateId]);
        return isset($model) ? $model->name : "";
    }
}
