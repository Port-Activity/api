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
    public $status = 'active';
    public $time_zone = null;

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
    public function set(
        string $email,
        string $passwordHash,
        string $firstName,
        string $lastName,
        int $roleId,
        string $last_login_time = null,
        string $last_login_data = null,
        string $last_session_time = null,
        string $registration_code_id = null
    ) {
        $this->email = $email;
        $this->password_hash = $passwordHash;
        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->role_id = $roleId;
        $this->last_login_time = $last_login_time;
        $this->last_login_data = $last_login_data;
        $this->last_session_time = $last_session_time;
        $this->registration_code_id = $registration_code_id;
    }

    public function getRole(): string
    {
        $roleRepository = new RoleRepository();
        $roles = $roleRepository->getMap();

        return array_search($this->role_id, $roles);
    }
}
