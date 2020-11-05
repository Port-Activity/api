<?php
namespace SMA\PAA\ORM;

class VesselModel extends OrmModel
{
    public $imo;
    public $vessel_name;
    public $visible = "t";

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setIsVisible(bool $isVisible)
    {
        $this->visible = $isVisible ? "t" : "f";
    }

    public function getIsVisible(): bool
    {
        return $this->visible === "t" || $this->visible === true;
    }

    public function set(
        int $imo,
        string $vesselName,
        bool $visible = true
    ) {
        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->setIsVisible($visible);
    }
}
