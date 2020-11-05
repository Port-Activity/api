<?php
namespace SMA\PAA\ORM;

class PortModel extends OrmModel
{
    public $name;
    public $service_id;
    public $whitelist_in;
    public $whitelist_out;
    public $locodes;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setIsWhiteListIn(bool $isWhiteListIn)
    {
        $this->whitelist_in = $isWhiteListIn ? "t" : "f";
    }

    public function getIsWhiteListIn(): bool
    {
        return $this->whitelist_in === "t" || $this->whitelist_in === true;
    }

    public function setIsWhiteListOut(bool $isWhiteListOut)
    {
        $this->whitelist_out = $isWhiteListOut ? "t" : "f";
    }

    public function getIsWhiteListOut(): bool
    {
        return $this->whitelist_out === "t" || $this->whitelist_out === true;
    }

    public function set(
        string $name,
        string $serviceId,
        bool $whitelistIn,
        bool $whitelistOut,
        array $locodes
    ) {
        $this->name = $name;
        $this->service_id = $serviceId;
        $this->setIsWhiteListIn($whitelistIn);
        $this->setIsWhiteListOut($whitelistOut);
        $this->locodes = json_encode($locodes);
    }
}
