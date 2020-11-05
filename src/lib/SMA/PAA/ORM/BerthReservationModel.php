<?php
namespace SMA\PAA\ORM;

class BerthReservationModel extends OrmModel
{
    public $berth_id;
    public $berth_reservation_type_id;
    public $reservation_start;
    public $reservation_end;
    public $slot_reservation_id;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $berthId,
        int $berthReservationTypeId,
        string $reservationStart,
        string $reservationEnd,
        int $slotReservationId = null
    ) {
        $this->berth_id = $berthId;
        $this->berth_reservation_type_id = $berthReservationTypeId;
        $this->reservation_start = $reservationStart;
        $this->reservation_end = $reservationEnd;
        $this->slot_reservation_id = $slotReservationId;
    }
}
