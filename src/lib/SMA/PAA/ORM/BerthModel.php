<?php
namespace SMA\PAA\ORM;

class BerthModel extends OrmModel
{
    public $code;
    public $name;
    public $nominatable = "f";

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setIsNominatable(bool $isNominatable)
    {
        $this->nominatable = $isNominatable ? "t" : "f";
    }

    public function getIsNominatable(): bool
    {
        return $this->nominatable === "t" || $this->nominatable === true;
    }

    public function set(
        string $code,
        string $name,
        bool $nominatable
    ) {
        $this->code = $code;
        $this->name = $name;
        $this->setIsNominatable($nominatable);
    }
}
