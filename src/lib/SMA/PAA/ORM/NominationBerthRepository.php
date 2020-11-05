<?php
namespace SMA\PAA\ORM;

class NominationBerthRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getBerthIds(int $nominationId): array
    {
        $res = [];

        $query = ["nomination_id" => $nominationId];
        $models = $this->list($query, 0, 1000);

        foreach ($models as $model) {
            $res[] = $model->berth_id;
        }

        return $res;
    }
}
