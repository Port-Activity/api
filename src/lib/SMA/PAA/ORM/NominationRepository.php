<?php
namespace SMA\PAA\ORM;

class NominationRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function findOpen(
        int $imo,
        string $startTime,
        string $endTime
    ): ?NominationModel {
        $query = [];
        $query["imo"] = $imo;
        $query["nomination_status_id"] = NominationStatusModel::id("open");
        $query["window_start"] = ["lte" => $startTime];
        $query["window_end"] = ["gte" => $endTime];

        return $this->first($query);
    }
}
