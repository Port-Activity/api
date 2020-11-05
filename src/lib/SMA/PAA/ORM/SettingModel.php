<?php
namespace SMA\PAA\ORM;

class SettingModel extends OrmModel
{
    public $name;
    public $value;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $name,
        string $value
    ) {
        $this->name = $name;
        $this->value = $value;
    }

    public function setValue(
        string $value
    ) {
        $this->value = $value;
    }
}
