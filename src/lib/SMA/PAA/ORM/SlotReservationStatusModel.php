<?php
namespace SMA\PAA\ORM;

class SlotReservationStatusModel extends OrmModel
{
    public $name;
    public $readable_name;

    public static function id(string $statusName, SlotReservationStatusRepository $repository = null): ?int
    {
        if ($repository === null) {
            $repository = new SlotReservationStatusRepository();
        }

        return $repository->mapStatusNameToStatusId($statusName);
    }

    public static function name(int $statusId, SlotReservationStatusRepository $repository = null): ?string
    {
        if ($repository === null) {
            $repository = new SlotReservationStatusRepository();
        }

        return $repository->mapStatusIdToStatusName($statusId);
    }

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
}
