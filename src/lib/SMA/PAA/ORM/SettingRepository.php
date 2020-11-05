<?php
namespace SMA\PAA\ORM;

class SettingRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getSetting(string $name): ?SettingModel
    {
        return $this->first(["name" => $name]);
    }
}
