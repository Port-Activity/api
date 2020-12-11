<?php
namespace SMA\PAA\ORM;

class UserModel extends OrmModel
{
    const STATUS_ACTIVE     = "active";
    const STATUS_API_USER   = "api_user";
    const STATUS_DELETED    = "deleted";

    public $email;
    public $password_hash;
    public $first_name;
    public $last_name;
    public $role_id;
    public $last_login_time;
    public $last_login_data;
    public $last_session_time;
    public $registration_code_id;
    public $status = "active";
    public $time_zone = null;
    public $failed_logins = 0;
    public $suspend_start = null;
    public $locked = "f";
    public $registration_type = "";

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function dontLogFields()
    {
        return array(
            "password_hash"
            ,"last_login_time"
            ,"last_login_data"
        );
    }

    public function setLocked(bool $locked)
    {
        $this->locked = $locked ? "t" : "f";
    }

    public function getLocked(): bool
    {
        return $this->locked === "t" || $this->locked === true;
    }

    public function set(
        string $email,
        string $passwordHash,
        string $firstName,
        string $lastName,
        int $roleId,
        string $lastLoginTime = null,
        string $lastLoginData = null,
        string $lastSessionTime = null,
        string $registrationCodeId = null,
        string $status = "active",
        string $timeZone = null,
        int $failedLogins = 0,
        string $suspendStart = null,
        bool $locked = false,
        string $registrationType = ""
    ) {
        $this->email = $email;
        $this->password_hash = $passwordHash;
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->role_id = $roleId;
        $this->last_login_time = $lastLoginTime;
        $this->last_login_data = $lastLoginData;
        $this->last_session_time = $lastSessionTime;
        $this->registration_code_id = $registrationCodeId;
        $this->status = $status;
        $this->time_zone = $timeZone;
        $this->failed_logins = $failedLogins;
        $this->suspend_start = $suspendStart;
        $this->setLocked($locked);
        $this->registration_type = $registrationType;
    }

    public function getRole(): string
    {
        $roleRepository = new RoleRepository();
        $roles = $roleRepository->getMap();

        return array_search($this->role_id, $roles);
    }
}
