<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\PortRepository;
use SMA\PAA\ORM\PortModel;

class PortService implements IPortService
{
    public function add(
        string $name,
        string $service_id,
        array $locodes
    ): array {
        $repository = new PortRepository();
        $model = new PortModel();
        $model->set($name, $service_id, true, true, $locodes);
        $repository->save($model);

        return ["result" => "OK"];
    }

    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        $res = [];

        $query = [];
        if (!empty($search)) {
            if (preg_match("/^\^/", $search)) {
                $query = ["name" => ["ilike" => substr($search, 1) . "%"]];
            } else {
                $query = ["name" => ["ilike" => "%" . $search . "%"]];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        $repository = new PortRepository();
        return $repository->listPaginated($query, $offset, $limit, $sort);
    }

    public function getByServiceId(string $service_id): ?PortModel
    {
        $repository = new PortRepository();
        return $repository->getByServiceId($service_id);
    }

    private function setWhiteList(string $func, string $service_id, $whitelist): array
    {
        $repository = new PortRepository();
        $model = $repository->getByServiceId($service_id);

        if (!isset($model)) {
            return ["result" => "ERROR"];
        }

        if ($whitelist === true) {
            $model->$func(true);
        } elseif ($whitelist === false) {
            $model->$func(false);
        } else {
            return ["result" => "ERROR"];
        }

        $repository->save($model, true);

        return ["result" => "OK"];
    }

    public function setWhiteListIn(string $service_id, $whitelist): array
    {
        return $this->setWhiteList("setIsWhiteListIn", $service_id, $whitelist);
    }

    public function setWhiteListOut(string $service_id, $whitelist): array
    {
        return $this->setWhiteList("setIsWhiteListOut", $service_id, $whitelist);
    }
}
