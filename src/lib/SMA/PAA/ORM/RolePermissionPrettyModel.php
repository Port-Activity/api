<?php
namespace SMA\PAA\ORM;

class RolePermissionPrettyModel extends RolePermissionModel
{
    public $role;
    public $permission;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setFromRolePermission(RolePermissionModel $rolePermissionModel)
    {
        $roleRepository = new RoleRepository();
        $roles = $roleRepository->getMap();
        $permissionRepository = new PermissionRepository();
        $permissions = $permissionRepository->getMap();

        $this->role_id = $rolePermissionModel->role_id;
        $this->permission_id = $rolePermissionModel->permission_id;
        $this->created_by = $rolePermissionModel->created_by;
        $this->created_at = $rolePermissionModel->created_at;
        $this->modified_by = $rolePermissionModel->modified_by;
        $this->modified_at = $rolePermissionModel->modified_at;

        $this->role = array_search($this->role_id, $roles);
        $this->permission = array_search($this->permission_id, $permissions);
    }
}
