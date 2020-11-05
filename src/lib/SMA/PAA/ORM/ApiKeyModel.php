<?php
namespace SMA\PAA\ORM;

class ApiKeyModel extends OrmModel
{
    public $name;
    public $key;
    public $is_active;
    public $bound_user_id;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $boundUserId,
        string $name,
        string $key,
        bool $isActive
    ) {
        $this->name = $name;
        $this->key = $key;
        $this->is_active = $isActive;
        $this->bound_user_id = $boundUserId;
    }
}
