<?php
namespace SMA\PAA\ORM;

class BerthReservationTypeRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function getStatusNameMappings(): Array
    {
        $res = [];
        $models = $this->listAll();

        foreach ($models as $model) {
            $res[$model->name] = $model->id;
        }

        return $res;
    }

    public function mapStatusNameToStatusId(string $statusName): ?int
    {
        $model = $this->first(["name" => $statusName]);
        return isset($model) ? $model->id : null;
    }

    public function mapStatusIdToStatusName(int $statusId): ?string
    {
        $model = $this->first(["id" => $statusId]);
        return isset($model) ? $model->name : null;
    }
}
