<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\BerthReservationModel;

interface IBerthReservationService
{
    public function add(
        int $berth_id,
        string $berth_reservation_type,
        string $reservation_start,
        string $reservation_end,
        int $slot_reservation_id = null
    ): array;
    public function update(
        int $id,
        string $reservation_start,
        string $reservation_end
    ): array;
    public function delete(int $id): array;
    public function get(int $id): ?BerthReservationModel;
    public function list(
        int $berth_id,
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
}
