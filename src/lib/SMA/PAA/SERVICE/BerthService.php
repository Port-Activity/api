<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\BerthRepository;
use SMA\PAA\ORM\BerthModel;
use SMA\PAA\InvalidParameterException;

class BerthService implements IBerthService
{
    private function convertNominatable($nominatable): bool
    {
        if ($nominatable === true || $nominatable === 1) {
            return true;
        }

        return false;
    }

    public function add(
        string $code,
        string $name,
        $nominatable
    ): array {
        $repository = new BerthRepository();
        $model = new BerthModel();
        $model->set($code, $name, $this->convertNominatable($nominatable));
        $repository->save($model);

        return ["result" => "OK"];
    }

    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array {
        $query = [];

        if (!empty($search)) {
            $query["complex_query"] = "code ILIKE ? OR name ILIKE ?";
            $search = "%" . $search . "%";
            $query["complex_args"] = [$search, $search];
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        $repository = new BerthRepository();
        return $repository->listPaginated($query, $offset, $limit, $sort);
    }

    public function update(
        int $id,
        string $code,
        string $name,
        $nominatable
    ): array {
        $repository = new BerthRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException(
                "Cannot find berth instance with given id: " . $id
            );
        }

        $model->set($code, $name, $this->convertNominatable($nominatable));
        $repository->save($model);

        return ["result" => "OK"];
    }

    public function delete(int $id): array
    {
        $repository = new BerthRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException(
                "Cannot find berth instance with given id: " . $id
            );
        }

        $repository->delete([$id]);

        return ["result" => "OK"];
    }

    public function get(int $id): ?BerthModel
    {
        $repository = new BerthRepository();
        return $repository->get($id);
    }
}
