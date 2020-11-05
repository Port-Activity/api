<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;

class PermissionRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getMap(): Array
    {
        $res = [];

        $results = $this->getMultipleWithQuery("SELECT * FROM {$this->table}");

        foreach ($results as $result) {
            $res[$result->name] = $result->id;
        }

        return $res;
    }

    public function mapToId($permissionName): int
    {
        $permissionMap = $this->getMap();
        if (array_key_exists($permissionName, $permissionMap)) {
            return $permissionMap[$permissionName];
        }
        throw new InvalidArgumentException("Invalid permission: " . $permissionName);
    }
}
