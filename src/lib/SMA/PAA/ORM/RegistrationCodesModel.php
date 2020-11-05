<?php
namespace SMA\PAA\ORM;

class RegistrationCodesModel extends OrmModel
{
    public $enabled;
    public $code;
    public $role;
    public $description;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setIsEnabled(bool $enabled)
    {
        $this->enabled = $enabled ? "t" : "f";
    }

    public function getIsEnabled(): bool
    {
        return $this->enabled === "t" || $this->enabled === true;
    }

    public function set(
        bool $enabled,
        string $code,
        string $role,
        string $description
    ) {
        $this->setIsEnabled($enabled);
        $this->code = $code;
        $this->role = $role;
        $this->description = $description;
    }
}
