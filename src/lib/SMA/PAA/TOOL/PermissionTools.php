<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\Session;
use SMA\PAA\ORM\UserModel;
use SMA\PAA\ORM\RoleRepository;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\ORM\RolePermissionRepository;

class PermissionTools
{
    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    public function hasPermission($permission, $bodyParameters)
    {
        if ($permission === "public") {
            return true;
        }
        $session = $this->session;

        if ($session->apiKey()) {
            //TODO: check role / permission
            if ($permission === "timestamp") {
                return true;
            } elseif ($permission === "translation") {
                return true;
            } elseif ($permission === "push_notification_token") {
                return true;
            } elseif ($permission === "export_api") {
                return true;
            } elseif ($permission === "update_sea_chart_location") {
                return true;
            }
        }

        // When processing login we don't have user ID yet
        if ($permission === "login") {
            if (isset($bodyParameters["email"])) {
                $userRepository = new UserRepository();
                $user = $userRepository->getWithEmail($bodyParameters["email"]);
                if (isset($user)) {
                    $rolePermissionRepository = new RolePermissionRepository();
                    $role = $user->getRole();
                    $permissions = $rolePermissionRepository->getRolePermissions($role);

                    if (array_search($permission, $permissions) !== false) {
                        return true;
                    }
                }
            }
        }

        if ($session->userId()) {
            $rolePermissionRepository = new RolePermissionRepository();
            $role = $session->user()->getRole();
            $permissions = $rolePermissionRepository->getRolePermissions($role);

            if (array_search($permission, $permissions) !== false) {
                return true;
            }

            // Manage user contains the management permissions of several user roles
            // Check that permission exists to manage at least one role
            // User service takes care of more detailed check
            if ($permission === "manage_user") {
                $roleRepository = new RoleRepository();
                $roleMap = $roleRepository->getMap();
                $rolePermissions = [];
                foreach ($roleMap as $key => $value) {
                    $rolePermissions[] = $permission . "_" . $key;
                }

                if (!empty(array_intersect($rolePermissions, $permissions))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasRoleManagementPermission(string $targetRole): bool
    {
        $session = $this->session;

        if ($session->userId()) {
            $user = $session->user();

            return $this->userHasRoleManagementPermission($user, $targetRole);
        }

        return false;
    }

    public function userHasRoleManagementPermission(UserModel $user, string $targetRole): bool
    {
        $rolePermissionRepository = new RolePermissionRepository();
        $role = $user->getRole();
        $permissions = $rolePermissionRepository->getRolePermissions($role);
        $neededPermission = "manage_user_" . $targetRole;

        if (array_search($neededPermission, $permissions) !== false) {
            return true;
        }

        return false;
    }

    public function userManagementLevel(): string
    {
        $session = $this->session;

        if ($session->userId()) {
            $user = $session->user();

            return $this->userUserManagementLevel($user);
        }

        return "";
    }

    public function userUserManagementLevel(UserModel $user): string
    {
        $rolePermissionRepository = new RolePermissionRepository();
        $role = $user->getRole();
        $permissions = $rolePermissionRepository->getRolePermissions($role);

        if (array_search("manage_all_user", $permissions) !== false) {
            return "all";
        } elseif (array_search("manage_own_user", $permissions) !== false) {
            return "own";
        }

        return "";
    }

    public function hasUserManagementPermission(UserModel $targetUser): bool
    {
        $session = $this->session;

        if ($session->userId()) {
            $user = $session->user();

            return $this->userHasUserManagementPermission($user, $targetUser);
        }

        return false;
    }

    public function userHasUserManagementPermission(UserModel $user, UserModel $targetUser): bool
    {
        $rolePermissionRepository = new RolePermissionRepository();
        $role = $user->getRole();
        $permissions = $rolePermissionRepository->getRolePermissions($role);

        $userManagementLevel = $this->userUserManagementLevel($user);
        if ($userManagementLevel === "all") {
            return true;
        } elseif ($userManagementLevel === "own") {
            if ($user->id === $targetUser->created_by) {
                return true;
            }
        }

        return false;
    }
}
