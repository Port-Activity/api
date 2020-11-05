<?php
namespace SMA\PAA\ORM;

class BerthReservationTypeModel extends OrmModel
{
    public $name;
    public $readable_name;

    public static function id(string $statusName, BerthReservationTypeRepository $repository = null): ?int
    {
        if ($repository === null) {
            $repository = new BerthReservationTypeRepository();
        }

        return $repository->mapStatusNameToStatusId($statusName);
    }

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
