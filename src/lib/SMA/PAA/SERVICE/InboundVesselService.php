<?php
namespace SMA\PAA\SERVICE;

use InvalidArgumentException;

use SMA\PAA\ORM\InboundVesselRepository;
use SMA\PAA\ORM\InboundVesselModel;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\ORM\PortRepository;
use SMA\PAA\ORM\PortModel;
use SMA\PAA\TOOL\VisTools;

class InboundVesselService implements IInboundVesselService
{
    private function isService(string $fromServiceId): bool
    {
        $portRepository = new PortRepository();
        $portModel = $portRepository->getByServiceId($fromServiceId);

        if (!isset($portModel)) {
            return false;
        }

        return true;
    }
    private function findServiceFromVis(string $fromServiceId)
    {
        $visVesselRepository = new VisVesselRepository();
        $visVesselModel = $visVesselRepository->getWithServiceId($fromServiceId);

        if (!isset($visVesselModel)) {
            new VisTools(new CurlRequest());
            $visTools->getService(null, $fromServiceId);
            $visVesselModel = $visVesselRepository->getWithServiceId($fromServiceId);
        }

        if (!isset($visVesselModel)) {
            throw new InvalidArgumentException(
                "Cannot find VIS service ID: " . $fromServiceId
            );
        }

        $portRepository = new PortRepository();
        $portModel = new PortModel();
        $portModel->set(
            $visVesselModel->vessel_name,
            $visVesselModel->service_id,
            true,
            true,
            []
        );

        $portRepository->save($portModel);
    }
    private function isServiceWhiteListedIn(string $fromServiceId): bool
    {
        $portRepository = new PortRepository();
        $portModel = $portRepository->getByServiceId($fromServiceId);

        if (!isset($portModel)) {
            throw new InvalidArgumentException(
                "Cannot find service ID: " . $fromServiceId
            );
        }

        return $portModel->getIsWhiteListIn();
    }
    public function add(
        string $time,
        int $imo,
        string $vesselName,
        string $fromServiceId
    ): int {
        $res = -1;

        if (!$this->isService($fromServiceId)) {
            $this->findServiceFromVis($fromServiceId);
        }

        if (!$this->isServiceWhiteListedIn($fromServiceId)) {
            throw new InvalidArgumentException(
                "Service ID is not whitelisted to post inbound vessel data: " . $fromServiceId
            );
        }

        $repository = new InboundVesselRepository();
        $model = new InboundVesselModel();
        $model->set($time, $imo, $vesselName, $fromServiceId);
        $repository->save($model);

        return $res;
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

        $repository = new InboundVesselRepository();
        $res = $repository->listPaginated($query, $offset, $limit, $sort);

        return $res;
    }
}
