<?php
namespace SMA\PAA\ORM;

class UserRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getWithEmail(string $email): ?UserModel
    {
        return $this->first(["email" => $email, "status" => "active"]);
    }
}
