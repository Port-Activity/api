<?php
namespace SMA\PAA\SERVICE;

use DateTime;
use SMA\PAA\Session;
use SMA\PAA\Server;
use SMA\PAA\ORM\PushNotificationTokenModel;
use SMA\PAA\ORM\PushNotificationTokenRepository;
use SMA\PAA\ORM\RoleRepository;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\ORM\UserModel;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\AuthenticationException;
use SMA\PAA\InvalidOperationException;
use SMA\PAA\ORM\RegistrationCodesModel;
use SMA\PAA\ORM\RegistrationCodesRepository;
use SMA\PAA\TOOL\PermissionTools;
use SMA\PAA\SERVICE\DateService;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\TOOL\PasswordTools;
use SMA\PAA\TOOL\PasswordRules;
use SMA\PAA\ORM\SettingRepository;

class UserService
{
    private $session;
    private $userModelWhitelist;
    private $userModelWhitelistMinimal;
    public function __construct(Session $session = null)
    {
        $this->userModelWhitelist = [
            "id",
            "first_name",
            "last_name",
            "email",
            "role_id",
            "registration_code_id",
            "created_at",
            "created_by",
            "modified_at",
            "modified_by",
            "status",
            "created_by_email",
            "locked",
            "suspended",
            "registration_type"
        ];

        $this->userModelWhitelistMinimal = [
            "first_name",
            "last_name",
            "email",
            "status"
        ];

        if (!$session) {
            $session = new Session();
        }
        $this->session = $session;
    }
    public function get(int $id): ?UserModel
    {
        $repository = new UserRepository();
        $res = $repository->get($id)->filter($this->userModelWhitelist);

        if ($res->status === UserModel::STATUS_DELETED) {
            throw new \Exception("No such user");
        }

        return $res;
    }
    public function getMinimal(int $id): ?UserModel
    {
        $repository = new UserRepository();
        $res = $repository->get($id)->filter($this->userModelWhitelistMinimal);

        if ($res->status === UserModel::STATUS_DELETED) {
            $res->email = "ex-user";
        }

        return $res;
    }
    public function add(
        string $email,
        string $first_name,
        string $last_name,
        string $role = "inactive_user"
    ): array {
        return $this->addUser($email, $first_name, $last_name, $role, null, true);
    }
    public function addWithoutPassword(
        string $email,
        string $first_name,
        string $last_name
    ): array {
        return $this->addUser($email, $first_name, $last_name, "inactive_user", null, false);
    }
    private function addUser(
        string $email,
        string $first_name,
        string $last_name,
        string $role = "inactive_user",
        string $password = null,
        bool $addPassword = false,
        bool $validRegistration = false
    ): array {
        if ($validRegistration === false) {
            $permissionTools = new PermissionTools(new Session());
            if (!$permissionTools->hasRoleManagementPermission($role)) {
                throw new AuthenticationException("No permission to add user with given role");
            }
        }

        //$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (!$first_name) {
            throw new InvalidParameterException("First name can't be empty");
        }

        if (!$last_name) {
            throw new InvalidParameterException("Last name can't be empty");
        }

        if (!$email) {
            throw new InvalidParameterException("Email can't be empty");
        }

        /* TODO: email verification disabled as this field can also be username
        if (!preg_match($patten, $email)) {
            throw new InvalidParameterException("Invalid email: " . $email);
        }*/

        $repository = new UserRepository();
        $testUser = $repository->first(["email" => ["LOWER" => $email]]);
        if ($testUser) {
            throw new InvalidParameterException("User with email $email already exists");
        }

        $auth = new AuthService();
        $model = new UserModel();
        $roleRepository = new RoleRepository();
        $model->role_id = $roleRepository->mapToId($role);
        $model->email = $email;
        $model->first_name = $first_name;
        $model->last_name = $last_name;
        $model->password_hash = ""; // note: password_hash can't be null
        $model->registration_type = "manual"; // note: code and codeless will be updated at later stage
        if ($addPassword) {
            $password = $auth->randomPassword();
            $model->password_hash = $auth->hash($password);
        } elseif ($password) {
            $model->password_hash = $auth->hash($password);
        }
        $newUserId = $repository->save($model);

        $server = new Server();
        if ($server->isDev()) {
            error_log("New user created with password: " . $password);
        }

        $returnValues = ["id" => $newUserId];
        if ($addPassword) {
            $returnValues["password"] = $password;
        }

        return $returnValues;
    }
    public function update(
        int $id,
        string $email,
        string $first_name,
        string $last_name,
        string $password = null,
        string $role = "inactive_user",
        bool $updateRole = false
    ): ?UserModel {
        return $this->updateUser(
            $id,
            $email,
            $first_name,
            $last_name,
            null,
            $role,
            $updateRole,
            false
        );
    }
    public function updateUser(
        int $id,
        string $email,
        string $firstName,
        string $lastName,
        string $password = null,
        string $role = "inactive_user",
        bool $updateRole = false,
        bool $fromResetPassword = false
    ): ?UserModel {
        if ($fromResetPassword === false) {
            $permissionTools = new PermissionTools(new Session());

            if (!$permissionTools->hasRoleManagementPermission($role)) {
                throw new AuthenticationException("No permission to update user to given role");
            }

            $userRole = $this->get($id)->getRole();
            if (!$permissionTools->hasRoleManagementPermission($userRole)) {
                throw new AuthenticationException("No permission to update user");
            }
        }

        if ($updateRole && $userRole === "admin" && $role !== "admin") {
            $repository = new UserRepository();
            $roleRepository = new RoleRepository();
            $adminRoleId = $roleRepository->mapToId("admin");
            $admins = $repository->listNoLimit(["role_id" => $adminRoleId], 0);
            if (count($admins) === 1) {
                throw new InvalidOperationException("Last admin role cannot be changed from API");
            }
        }

        //$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

        if (!$id) {
            throw new InvalidParameterException("Invalid ID");
        }
        if (!$firstName) {
            throw new InvalidParameterException("First name can't be empty");
        }

        if (!$lastName) {
            throw new InvalidParameterException("Last name can't be empty");
        }

        if (!$email) {
            throw new InvalidParameterException("Email can't be empty");
        }

        /* TODO: email verification disabled as this field can also be username
        if (!preg_match($pattern, $email)) {
            throw new InvalidParameterException("Invalid email: " . $email);
        }*/

        $repository = new UserRepository();
        $model = $repository->get($id);
        if (!$model) {
            // Don't reveal whether user actually exists
            throw new InvalidParameterException("Invalid ID");
        }

        if ($fromResetPassword === false) {
            if (!$permissionTools->hasUserManagementPermission($model)) {
                throw new AuthenticationException("No permission to update given user");
            }
        }

        // Don't allow to change to existing email
        // Allow changing case of own email
        $testUser = $repository->first(["email" => ["LOWER" => $email]]);
        if ($testUser && $testUser->id !== $model->id) {
            throw new InvalidParameterException("User with email $email already exists");
        }
        $model->email = $email;
        $model->first_name = $firstName;
        $model->last_name = $lastName;
        if ($password) {
            $auth = new AuthService();
            $model->password_hash = $auth->hash($password);
        }
        // Update role
        if ($updateRole) {
            $roleRepository = new RoleRepository();
            $model->role_id = $roleRepository->mapToId($role);
        }

        $repository->save($model);

        return $this->get($id);
    }
    public function delete(int $id)
    {
        $session = $this->session;

        if ($session->userId() === $id) {
            throw new InvalidOperationException("You can't delete yourself");
        }

        $permissionTools = new PermissionTools($session);
        $userRole = $this->get($id)->getRole();
        if (!$permissionTools->hasRoleManagementPermission($userRole)) {
            throw new AuthenticationException("No permission to delete user");
        }

        $roleRepository = new RoleRepository();
        $repository = new UserRepository();

        if ($userRole === "admin") {
            $roleRepository = new RoleRepository();
            $adminRoleId = $roleRepository->mapToId("admin");
            $admins = $repository->listNoLimit(["role_id" => $adminRoleId], 0);
            if (count($admins) === 1) {
                throw new InvalidOperationException("Last admin cannot be deleted from API");
            }
        }

        $user = $repository->get($id);

        if (!$permissionTools->hasUserManagementPermission($user)) {
            throw new AuthenticationException("No permission to delete given user");
        }

        $user->email = $user->id . "@deleted";
        $user->password_hash = "";
        $user->first_name = "";
        $user->last_name = "";
        $user->role_id = $roleRepository->mapToId("inactive_user");
        // $user->last_login_time = "";
        $user->last_login_data = '[]';
        $user->status = UserModel::STATUS_DELETED;

        $repository->save($user);
        return true;
    }
    public function list($limit, $offset, $sort, $search = '')
    {
        $res = [];
        $query = ["public.user.status" => "active"];

        $sorts = explode(",", $sort);
        $newSort = "";
        foreach ($sorts as $sort) {
            if (strpos($sort, "lower(") !== false) {
                $newSort .= str_replace("lower(", "lower(public.user.", $sort) . ",";
            } else {
                $newSort .= "public.user." . $sort . ",";
            }
        }
        $newSort = rtrim($newSort, ",");

        if (!empty($search)) {
            $query["complex_query"] =
                "public.user.status=? " .
                "AND (public.user.email ILIKE ? " .
                "OR public.user.first_name ILIKE ? " .
                "OR public.user.last_name ILIKE ?)";
            $search = "%" . $search . "%";
            $query["complex_args"] = ["active", $search, $search, $search];
        }

        $session = new Session();
        $userId = $session->userId();
        $permissionTools = new PermissionTools(new Session());
        $userManagementLevel = $permissionTools->userManagementLevel();

        if ($userManagementLevel === "all") {
            // Do nothing
        } elseif ($userManagementLevel === "own") {
            $query["public.user.created_by"] = $userId;
            if (!empty($search)) {
                $query["complex_query"] = "public.user.created_by=? AND " . $query["complex_query"];
                array_unshift($query["complex_args"], $userId);
            }
        } else {
            return ["data" => [], "pagination" => ["start" => 0, "limit" => 0, "total" => 0]];
        }

        $repository = new UserRepository();

        $joins = [];
        $joins["UserRepository"] = ["values" => ["email" => "created_by_email"], "join" => ["created_by" => "id"]];
        $query["complex_select"] = $repository->buildJoinSelect($joins);

        $rawResults = $repository->listPaginated($query, $offset, $limit, $newSort);
        foreach ($rawResults["data"] as $rawResult) {
            $rawResult->locked = $rawResult->getLocked();
            $rawResult->suspended = $this->isUserSuspended($rawResult->id);
            $res["data"][] = $rawResult->filter($this->userModelWhitelist);
        }
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }
    public function registerPushToken($installation_id, $platform, $push_token)
    {
        $session = new Session();
        $id = $session->userId();
        if (!$id) {
            throw new InvalidParameterException("Invalid ID");
        }
        if (!$installation_id) {
            throw new InvalidParameterException("Invalid Device Identifier");
        }
        if (!$platform) {
            throw new InvalidParameterException("Invalid Device Platform");
        }

        $repository = new UserRepository();
        $model = $repository->get($id);
        if (!$model) {
            // Don't reveal whether user actually exists
            throw new InvalidParameterException("Invalid ID");
        }

        // Insert or update push notification token
        $pushModel = new PushNotificationTokenModel();
        $pushModel->set($id, $installation_id, $platform, $push_token);
        $pushRepository = new PushNotificationTokenRepository();

        return $pushRepository->save($pushModel);
    }

    public function register($first_name, $last_name, $code, $email, $password)
    {
        if (!$code) {
            throw new InvalidParameterException("Invalid code");
        }

        // Check that code exists
        $repository = new RegistrationCodesRepository();
        $registrationCodesModel = $repository->getByCode($code);

        if ($registrationCodesModel && $registrationCodesModel->getIsEnabled()) {
            $passwordTools = new PasswordTools();
            $passwordTools->checkPasswordIsOk($email, $password, $registrationCodesModel->role);

            $createdBy = $registrationCodesModel->created_by;
            $userRepository = new UserRepository();
            $createdByUser = $userRepository->get($createdBy);
            if (isset($createdByUser)) {
                $permissionTools = new PermissionTools(new Session());
                if (!$permissionTools->userHasRoleManagementPermission($createdByUser, $registrationCodesModel->role)) {
                    throw new AuthenticationException("Code creator has no permission to create code with given role");
                }
            } else {
                throw new AuthenticationException("Code creator does not exist");
            }

            $res = $this->addUser(
                $email,
                $first_name,
                $last_name,
                $registrationCodesModel->role,
                $password,
                false,
                true
            );
            if ($res && $res['id']) {
                $repository = new UserRepository();
                $user = $repository->get($res['id']);
                if ($user) {
                    $this->updateUserRegistrationCodeId($user, $registrationCodesModel);
                    session_destroy();
                    $session = new Session();
                    $session->startWithUser($user);
                    $auth = new AuthService();
                    return array_merge([
                        "session_id" => session_id()
                    ], $auth->session());
                }
            }
        }
        throw new InvalidParameterException("Invalid code");
    }
    public function codelessRegister($first_name, $last_name, $email, $password)
    {
        $settingRepository = new SettingRepository();
        $settingModel = $settingRepository->getSetting("codeless_registration_module");

        if ($settingModel === null) {
            throw new InvalidParameterException("Codeless registration not permitted");
        }

        if ($settingModel->value === "disabled") {
            throw new InvalidParameterException("Codeless registration not permitted");
        }

        $passwordTools = new PasswordTools();
        $passwordTools->checkPasswordIsOk($email, $password, "user");

        $createdBy = $settingModel->modified_by;
        $userRepository = new UserRepository();
        $createdByUser = $userRepository->get($createdBy);
        if (isset($createdByUser)) {
            $permissionTools = new PermissionTools(new Session());
            if (!$permissionTools->userHasRoleManagementPermission($createdByUser, "user")) {
                throw new AuthenticationException(
                    "Codeless registration activator has no permission to allow user creation"
                );
            }
        } else {
            throw new AuthenticationException("Codeless registration activator does not exist");
        }

        $res = $this->addUser(
            $email,
            $first_name,
            $last_name,
            "user",
            $password,
            false,
            true
        );
        if ($res && $res['id']) {
            $repository = new UserRepository();
            $user = $repository->get($res['id']);
            if ($user) {
                $user->registration_type = "codeless";
                $user->created_by = $createdBy;
                $repository->save($user);
                session_destroy();
                $session = new Session();
                $session->startWithUser($user);
                $auth = new AuthService();
                return array_merge([
                    "session_id" => session_id()
                ], $auth->session());
            }
        }

        throw new InvalidParameterException("Codeless registration failed");
    }
    public function updateUserLastLogin(int $id)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        $dateService = new DateService();
        $model->last_login_time = $dateService->now();

        $data = [];
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $data["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
        }
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $data["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $data["HTTP_X_FORWARDED_FOR"] = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        $model->last_login_data = json_encode($data);

        $repository->save($model, true);
    }
    public function updateUserLastKnownTimeZone(int $id, string $timeZone)
    {
        $tools = new DateTools();
        if ($tools->isValidTimeZone($timeZone)) {
            $repository = new UserRepository();
            $model = $repository->get($id);
            if ($model->time_zone !== $timeZone) {
                $model->time_zone = $timeZone;
                $repository->save($model);
            }
        }
    }
    public function updateUserLastSessionTime(int $id)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        $dateService = new DateService();
        $model->last_session_time = $dateService->now();

        $repository->save($model, true);
    }
    public function updateUserRegistrationCodeId(UserModel $user, RegistrationCodesModel $registrationCodeModel)
    {
        $repository = new UserRepository();
        $user->created_by = $registrationCodeModel->created_by;
        $user->registration_code_id = $registrationCodeModel->id;
        $user->registration_type = "code";
        $repository->save($user);
    }
    private function lockOrSuspend(UserModel $user): UserModel
    {
        $passwordRules = new PasswordRules();
        $role = $user->getRole();

        $suspendFailures = $passwordRules->getSuspendFailures($role);
        $lockFailures = $passwordRules->getLockFailures($role);

        if ($user->failed_logins >= $lockFailures) {
            $user->setLocked(true);
            $user->suspend_start = null;
        } elseif ($user->failed_logins % $suspendFailures === 0) {
            $dateTools = new DateTools();
            $user->suspend_start = $dateTools->now();
        }

        return $user;
    }
    public function isUserSuspended(int $id)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        if ($model->suspend_start !== null) {
            $passwordRules = new PasswordRules();
            $role = $model->getRole();

            $suspendMinutes = $passwordRules->getSuspendMinutes($role);
            $suspendIsoDuration = "PT" . $suspendMinutes . "M";

            $dateTools = new DateTools();
            $nowDateTime = new DateTime($dateTools->now());
            $suspendEndDateTime = new DateTime($dateTools->addIsoDuration($model->suspend_start, $suspendIsoDuration));

            if ($suspendEndDateTime >= $nowDateTime) {
                return true;
            }
        }

        return false;
    }
    public function updateUserSuccessfulLogin(int $id)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        $model->failed_logins = 0;
        $model->suspend_start = null;
        $model->setLocked(false);

        $repository->save($model);
    }
    public function updateUserFailedLogin(int $id)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        $model->failed_logins = $model->failed_logins + 1;

        $model = $this->lockOrSuspend($model);

        $repository->save($model);
    }

    public function setUserSuspend(int $id, $suspended)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException("Invalid ID");
        }

        $permissionTools = new PermissionTools(new Session());
        $role = $model->getRole();

        if (!$permissionTools->hasRoleManagementPermission($role)) {
            throw new AuthenticationException("No permission to set suspend state of given user");
        }

        if (!$permissionTools->hasUserManagementPermission($model)) {
            throw new AuthenticationException("No permission to set suspend state of given user");
        }

        $dateTools = new DateTools();
        if ($suspended === true) {
            $model->suspend_start = $dateTools->now();
        } elseif ($suspended === false) {
            $model->suspend_start = null;
        } else {
            throw new InvalidParameterException("Invalid suspended parameter");
        }

        return $repository->save($model);
    }

    public function setUserLock(int $id, $locked)
    {
        $repository = new UserRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException("Invalid ID");
        }

        $permissionTools = new PermissionTools(new Session());
        $role = $model->getRole();

        if (!$permissionTools->hasRoleManagementPermission($role)) {
            throw new AuthenticationException("No permission to set lock state of given user");
        }

        if (!$permissionTools->hasUserManagementPermission($model)) {
            throw new AuthenticationException("No permission to set lock state of given user");
        }

        if ($locked === true) {
            $model->suspend_start = null;
            $model->setLocked(true);
        } elseif ($locked === false) {
            $model->failed_logins = 0;
            $model->suspend_start = null;
            $model->setLocked(false);
        } else {
            throw new InvalidParameterException("Invalid locked parameter");
        }

        return $repository->save($model);
    }
}
