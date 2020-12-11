<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VesselTypeModel;

class FakeVesselTypeRepository
{
    public $listReturnValue;

    public function save(OrmModel $model, bool $skipCrudLog = false): int
    {
        return 1;
    }

    public function getMarkerType(int $id, int $default = 1): int
    {
        return ($id + 10);
    }

    public function getVesselTypeName(int $id): string
    {
        return ($id === 1) ? 'Vessel type desc 1' : 'Vessel type desc 2';
    }

    public function list(array $query, int $start, int $count, string $orderBy = "id"): ?array
    {
        return $this->listReturnValue;
    }
}
