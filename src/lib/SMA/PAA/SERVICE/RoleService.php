<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\RoleRepository;

class RoleService
{
    public function get(): array
    {
        $res = [];

        $repository = new RoleRepository();
        $models = $repository->listAll();
        if (!empty($models)) {
            foreach ($models as $model) {
                $innerRes = [];
                $innerRes["id"] = $model->id;
                $innerRes["name"] = $model->name;
                $innerRes["readable_name"] = $model->readable_name;

                $res[] = $innerRes;
            }
        }

        return $res;
    }
}
