<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\InboundVesselModel;
use SMA\PAA\ORM\VesselModel;
use SMA\PAA\ORM\VesselRepository;

class InboundVesselRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if ($model->imo !== 0) {
            return $this->saveValidImo($model, $skipCrudLog);
        } else {
            return $this->saveWithoutImo($model, $skipCrudLog);
        }
    }

    public function saveValidImo(InboundVesselModel $model, bool $skipCrudLog = false)
    {
        # Keep vessel name in sync with vessel repository
        # Keep vessel repository in sync with inbound vessel repository
        $vesselRepository = new VesselRepository();
        $vesselModel = $vesselRepository->first(["imo" => $model->imo]);
        if (isset($vesselModel)) {
            $model->vessel_name = $vesselModel->vessel_name;
        } else {
            $vesselModel = new VesselModel();
            $vesselModel->set($model->imo, $model->vessel_name);
            $vesselRepository->save($vesselModel, $skipCrudLog);
        }

        # If database ID is explicitly set
        # then allow normal modification
        # todo: This can lead to non unique service_id instances
        if (isset($model->id)) {
            return parent::save($model, $skipCrudLog);
        }

        # Check if imo already exists in database
        $inboundVesselModel = $this->first(["imo" => $model->imo]);

        # If imo is already stored, then just update other information
        if (isset($inboundVesselModel)) {
            $inboundVesselModel->set(
                $model->time,
                $inboundVesselModel->imo,
                $inboundVesselModel->vessel_name,
                $model->from_service_id,
            );
            
            return parent::save($inboundVesselModel);
        }

        # Save new instance
        return parent::save($model);
    }

    public function saveWithoutImo(InboundVesselModel $model, bool $skipCrudLog = false)
    {
        # Check if vessel name already exists in database
        # Database will not have entries without IMO
        $vesselRepository = new VesselRepository();
        $vesselModel = $vesselRepository->first(["vessel_name" => ["LOWER" => $model->vessel_name]]);
        if (isset($vesselModel)) {
            $model->imo = $vesselModel->imo;
            return $this->saveValidImo($model, $skipCrudLog);
        }

        # If all else fails then store vessel name with fake IMO to database
        $vesselModel = new VesselModel();
        $vesselModel->set($model->imo, $model->vessel_name);
        $vesselRepository->save($vesselModel, $skipCrudLog);

        # Recursion to ultimately save new entry
        return $this->saveWithoutImo($model, $skipCrudLog);
    }
}
