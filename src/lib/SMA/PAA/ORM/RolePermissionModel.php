<?php
namespace SMA\PAA\ORM;

class RolePermissionModel extends OrmModel
{
    public $role_id;
    public $permission_id;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $roleId,
        int $permissionId
    ) {
        $this->role_id = $roleId;
        $this->permission_id = $permissionId;
    }
}
