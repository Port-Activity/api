<?php
namespace SMA\PAA\SERVICE;

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
            "status"
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
        $query = ["status" => "active"];
        if (!empty($search)) {
            $query["complex_query"] = "status=? AND (email ILIKE ? OR first_name ILIKE ? OR last_name ILIKE ?)";
            $search = "%" . $search . "%";
            $query["complex_args"] = ["active", $search, $search, $search];
        }

        $repository = new UserRepository();

        $rawResults = $repository->listPaginated($query, $offset, $limit, $sort);
        foreach ($rawResults["data"] as $rawResult) {
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

        $passwordTools = new PasswordTools();
        $passwordTools->checkPasswordIsOk($email, $password);

        // Check that code exists
        $repository = new RegistrationCodesRepository();
        $registrationCodesModel = $repository->getByCode($code);
        if ($registrationCodesModel && $registrationCodesModel->getIsEnabled()) {
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
        $user->registration_code_id = $registrationCodeModel->id;
        $repository->save($user);
    }
}
