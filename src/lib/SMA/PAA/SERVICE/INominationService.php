<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\NominationModel;

interface INominationService
{
    public function ownAdd(
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array;
    public function ownUpdate(
        int $id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array;
    public function ownDelete(int $id): array;
    public function ownGet(int $id): ?NominationModel;
    public function ownList(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
    public function getAllNominatableBerths(): array;
    public function getConsigneeUsers(): array;
    public function addForConsignee(
        int $consignee_user_id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array;
    public function update(
        int $id,
        string $company_name,
        string $email,
        int $imo,
        string $vessel_name,
        string $window_start,
        string $window_end,
        array $berth_ids
    ): array;
    public function delete(int $id): array;
    public function get(int $id): ?NominationModel;
    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
}
