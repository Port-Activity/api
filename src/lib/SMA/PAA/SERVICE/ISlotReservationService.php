<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\SlotReservationModel;

interface ISlotReservationService
{
    public function add(
        string $email,
        int $imo,
        string $vesselName,
        float $loa,
        float $beam,
        float $draft,
        string $laytime,
        string $eta
    ): array;
    public function update(
        int $id,
        string $laytime,
        string $jit_eta,
        string $rta_window_start = null
    ): array;
    public function delete(int $id, bool $cancel_only, bool $by_vessel): array;
    public function get(int $id): ?SlotReservationModel;
    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
    public function dashboardList(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
}
