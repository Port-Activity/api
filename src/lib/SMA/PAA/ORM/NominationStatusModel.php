<?php
namespace SMA\PAA\ORM;

class NominationStatusModel extends OrmModel
{
    public $name;
    public $readable_name;

    public static function id(string $statusName, NominationStatusRepository $repository = null): ?int
    {
        if ($repository === null) {
            $repository = new NominationStatusRepository();
        }

        return $repository->mapStatusNameToStatusId($statusName);
    }

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
