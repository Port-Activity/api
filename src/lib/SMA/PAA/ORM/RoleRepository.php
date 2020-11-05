<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;

class RoleRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getMap(): array
    {
        $res = [];

        $results = $this->getMultipleWithQuery("SELECT * FROM {$this->table}");

        foreach ($results as $result) {
            $res[$result->name] = $result->id;
        }

        return $res;
    }

    public function mapToId($roleName): int
    {
        $roleMap = $this->getMap();
        if (array_key_exists($roleName, $roleMap)) {
            return $roleMap[$roleName];
        }
        throw new InvalidArgumentException("Invalid role: " . $roleName);
    }
}
