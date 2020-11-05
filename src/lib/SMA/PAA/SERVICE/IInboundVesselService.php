<?php
namespace SMA\PAA\SERVICE;

interface IInboundVesselService
{
    public function add(string $time, int $imo, string $vesselName, string $fromServiceId): int;
    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
}
