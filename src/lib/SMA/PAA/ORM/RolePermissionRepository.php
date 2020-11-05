<?php
namespace SMA\PAA\ORM;

class RolePermissionRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getRolePermissions($roleName): array
    {
        $res = [];

        $roleRepository = new RoleRepository();
        $role_id = $roleRepository->mapToId($roleName);

        $rawResults = $this->listNoLimit(["role_id" => $role_id], 0);

        foreach ($rawResults as $rawResult) {
            $prettyResult = new RolePermissionPrettyModel();
            $prettyResult->setFromRolePermission($rawResult);
            $res[] = $prettyResult->permission;
        }

        return $res;
    }
}
