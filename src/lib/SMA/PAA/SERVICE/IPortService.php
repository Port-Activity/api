<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\PortModel;

interface IPortService
{
    public function add(
        string $name,
        string $service_id,
        array $locodes
    ): array;

    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;

    public function getByServiceId(string $service_id): ?PortModel;

    public function setWhiteListIn(string $service_id, $whitelist): array;
    public function setWhiteListOut(string $service_id, $whitelist): array;
}
