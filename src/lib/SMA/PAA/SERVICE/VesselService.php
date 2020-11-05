<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VesselRepository;
use SMA\PAA\ORM\VesselModel;
use SMA\PAA\SERVICE\StateService;

class VesselService implements IVesselService
{
    public function vessel(int $imo): ?VesselModel
    {
        /*
                // TODO: this kind of datas we expect in ui
                $data = [
                    "imo" => $imo,
                    "vessel_name" => "IMO " . $imo,
                    "nationality" => "FIN",
                    "from" => "Gävle",
                    "to" => "Rauma",
                    "arrival" => "2019-10-25T12:30:00+00:00",
                    "type" => "Bulk Carrier",
                    "status" => "At Gävle",
                    "loa" => "230 meters",
                    "bow" => "37 meters",
                    "draft" => "6.1 meters"
                ];
        */
        $repository = new VesselRepository();
        $res = $repository->first(["imo" => $imo]);
        unset($res->created_at);
        unset($res->created_by);
        unset($res->modified_at);
        unset($res->modified_by);
        return $res;
    }
    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ) {
        $res = [];

        $query = [];
        if (!empty($search)) {
            if (ctype_digit($search) && preg_match("/[0-9]{7,}/", $search)) {
                $query = ["imo" => $search];
            } elseif (preg_match("/^\^/", $search)) {
                $query = ["vessel_name" => ["ilike" => substr($search, 1) . "%"]];
            } else {
                $query = ["vessel_name" => ["ilike" => "%" . $search . "%"]];
            }
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        $vesselRepository = new vesselRepository();
        $res = $vesselRepository->listPaginated($query, $offset, $limit, $sort);

        return $res;
    }
    public function filter($object)
    {
        unset($object->created_at);
        unset($object->created_by);
        unset($object->modified_at);
        unset($object->modified_by);
        return $object;
    }
    public function setVesselVisibility(int $imo, $visible)
    {
        $repository = new VesselRepository();
        $model = $repository->getWithImo($imo);

        if ($visible === true) {
            $model->setIsVisible(true);
        } elseif ($visible === false) {
            $model->setIsVisible(false);
        } else {
            return ["result" => "ERROR"];
        }

        $repository->save($model);

        $service = new StateService();
        $service->triggerPortCalls();

        return ["result" => "OK"];
    }
}
