<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\Session;
use SMA\PAA\AuthenticationException;
use SMA\PAA\InvalidOperationException;
use SMA\PAA\TOOL\PermissionTools;
use SMA\PAA\ORM\RoleRepository;
use SMA\PAA\ORM\PermissionRepository;
use SMA\PAA\ORM\RolePermissionRepository;
use SMA\PAA\ORM\RolePermissionModel;

class RolePermissionService
{
    private function checkPermissionAndMapIds(string $role, string $permission): array
    {
        $res = [];

        if ($role === "admin") {
            throw new InvalidOperationException("Admin permissions cannot be changed with API");
        }

        $roleRepository = new RoleRepository();
        $res["role"] = $roleRepository->mapToId($role);

        $permissionRepository = new PermissionRepository();
        $res["permission"] = $permissionRepository->mapToId($permission);

        $permissionTools = new PermissionTools(new Session());
        if (!$permissionTools->hasRoleManagementPermission($role)) {
            throw new AuthenticationException("No permission to modify permissions of given role");
        }

        return $res;
    }

    public function getAll(): array
    {
        $res = [];

        $roleRepository = new RoleRepository();
        $roleModels = $roleRepository->listAll();

        $repository = new RolePermissionRepository();

        foreach ($roleModels as $roleModel) {
            $innerRes = [];
            $innerRes[$roleModel->name] = $repository->getRolePermissions($roleModel->name);

            $res[] = $innerRes;
        }

        return $res;
    }

    public function getByRole(string $role): array
    {
        $res = [];

        $repository = new RolePermissionRepository();
        $res[$role] = $repository->getRolePermissions($role);

        return $res;
    }

    public function add(string $role, string $permission)
    {
        $ids = $this->checkPermissionAndMapIds($role, $permission);

        $repository = new RolePermissionRepository();
        $existingPermissions = $repository->getRolePermissions($role);
        if (array_search($permission, $existingPermissions) !== false) {
            return ["result" => "OK"];
        }

        $model = new RolePermissionModel();
        $model->set($ids["role"], $ids["permission"]);

        if ($repository->save($model)) {
            return ["result" => "OK"];
        }
    }

    public function delete(string $role, string $permission)
    {
        $ids = $this->checkPermissionAndMapIds($role, $permission);

        $repository = new RolePermissionRepository();
        $existingPermissions = $repository->getRolePermissions($role);
        if (array_search($permission, $existingPermissions) === false) {
            return ["result" => "OK"];
        }

        $model = $repository->first(["role_id" => $ids["role"], "permission_id" => $ids["permission"]]);
        $delIds = [];
        $delIds[] = $model->id;

        if ($repository->delete($delIds)) {
            return ["result" => "OK"];
        }
    }
}
