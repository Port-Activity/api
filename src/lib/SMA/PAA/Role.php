<?php
namespace SMA\PAA;

class Role
{

    private $role = null;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function role()
    {
        return $this->role;
    }

    public function equals(Role $role)
    {
        return $role->Role() === $this->role;
    }

    final public static function PUBLIC(): Role
    {
        return new Role("public");
    }
    final public static function ADMIN(): Role
    {
        return new Role("admin");
    }
    final public static function SECONDADMIN(): Role
    {
        return new Role("second_admin");
    }
    final public static function FIRSTUSER(): Role
    {
        return new Role("first_user");
    }
    final public static function USER(): Role
    {
        return new Role("user");
    }
}
