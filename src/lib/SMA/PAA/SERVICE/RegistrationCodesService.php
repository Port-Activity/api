<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\Session;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\AuthenticationException;
use SMA\PAA\TOOL\PermissionTools;
use SMA\PAA\ORM\RegistrationCodesModel;
use SMA\PAA\ORM\RegistrationCodesRepository;

class RegistrationCodesService
{
    private function createCode(): string
    {
        // O and I not included to avoid mixing with 0 and 1
        $keyspace = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        $pieces = [];
        $max = mb_strlen($keyspace, "8bit") - 1;
        for ($i = 0; $i < 6; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        $alphabetical = implode("", $pieces);

        $numerical = random_int(100000, 999999);

        return "{$alphabetical}-{$numerical}";
    }

    public function add($role, $description)
    {
        $permissionTools = new PermissionTools(new Session());
        if (!$permissionTools->hasRoleManagementPermission($role)) {
            throw new AuthenticationException("No permission to add registration code for given role");
        }

        $available = false;
        $id = null;
        $repository = new RegistrationCodesRepository();
        $registrationCodesModel = new RegistrationCodesModel();
        while (!$available) {
            $code = $this->createCode();
            $registrationCodesModel->set(true, $code, $role, $description);
            $available = !$repository->exists($registrationCodesModel);
            if ($available) {
                $id = $repository->save($registrationCodesModel);
            }
        }
        return $id;
    }

    public function update(string $id, int $enabled, string $role)
    {
        $session = new Session();
        $userId = $session->userId();
        $permissionTools = new PermissionTools($session);
        if (!$permissionTools->hasRoleManagementPermission($role)) {
            throw new AuthenticationException("No permission to update registration code to given role");
        }

        $repository = new RegistrationCodesRepository();
        $registrationCodesModel = $repository->get($id);
        if (!$registrationCodesModel) {
            throw new InvalidParameterException("Invalid ID");
        }

        $userManagementLevel = $permissionTools->userManagementLevel();
        if ($userManagementLevel === "all") {
            // Do nothing
        } elseif ($userManagementLevel === "own") {
            if ($userId !== $registrationCodesModel->created_by) {
                throw new InvalidParameterException("No permission to update given registration code");
            }
        } else {
            throw new InvalidParameterException("No permission to update given registration code");
        }

        $registrationCodesModel->setIsEnabled($enabled);
        $registrationCodesModel->role = $role;
        $id = $repository->save($registrationCodesModel);

        return $id;
    }

    public function delete(int $id)
    {
        $session = new Session();
        $userId = $session->userId();
        $permissionTools = new PermissionTools($session);

        $repository = new RegistrationCodesRepository();
        $registrationCodesModel = $repository->get($id);
        if (!$registrationCodesModel) {
            throw new InvalidParameterException("Invalid ID");
        }

        $userManagementLevel = $permissionTools->userManagementLevel();
        if ($userManagementLevel === "all") {
            // Do nothing
        } elseif ($userManagementLevel === "own") {
            if ($userId !== $registrationCodesModel->created_by) {
                throw new InvalidParameterException("No permission to update given registration code");
            }
        } else {
            throw new InvalidParameterException("No permission to update given registration code");
        }

        return $repository->delete([$id]);
    }

    public function list($limit, $offset, $sort, $search = ""): array
    {
        $res = [];

        $session = new Session();
        $userId = $session->userId();
        $permissionTools = new PermissionTools($session);

        $query = [];

        $repository = new RegistrationCodesRepository();

        $joins = [];
        $joins["RoleRepository"] = ["values" => ["readable_name"], "join" => ["role" => "name"]];
        $joins["UserRepository"] = ["values" => ["email"], "join" => ["created_by" => "id"]];
        $query["complex_select"] = $repository->buildJoinSelect($joins);

        if ($search) {
            $query["description"] = ["ilike" => "%" . $search . "%"];
        }

        $userManagementLevel = $permissionTools->userManagementLevel();
        if ($userManagementLevel === "all") {
            // Do nothing
        } elseif ($userManagementLevel === "own") {
            $query["public.registration_codes.created_by"] = $userId;
        } else {
            return ["data" => [], "pagination" => ["start" => 0, "limit" => 0, "total" => 0]];
        }

        return $repository->listPaginated($query, $offset, $limit, $sort);
    }
}
