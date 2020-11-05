<?php
namespace SMA\PAA\ORM;

class RegistrationCodesRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function exists(RegistrationCodesModel $needle): bool
    {
        return $this->first([
            "code" => $needle->code,
        ]) !== null;
    }

    public function getByCode(string $code): ?RegistrationCodesModel
    {
        return $this->getWithQuery("SELECT * FROM {$this->table} "
        . "WHERE code=?", $code);
    }
}
