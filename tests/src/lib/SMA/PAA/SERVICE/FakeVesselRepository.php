<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VesselModel;
use SMA\PAA\ORM\OrmModel;
use SMA\PAA\InvalidParameterException;
use Exception;

class FakeVesselRepository
{
    public $getReturnValue = null;
    public $saveReturnValue = true;
    public $saveThrows = false;

    public function getWithImo(int $imo): VesselModel
    {
        if ($imo === 999999) {
            throw new InvalidParameterException("Invalid IMO");
        }

        $vesselModel = new VesselModel();
        $vesselModel->vessel_type = ($imo === 111111) ? 1 : 2;
        return $vesselModel;
    }

    public function getVesselName(int $imo): string
    {
        return "Fake";
    }

    public function get(int $id): ?OrmModel
    {
        return $this->getReturnValue ? new OrmModel('') : null;
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if ($this->saveThrows) {
            throw new \Exception("Save failed");
        }
        return $this->saveReturnValue;
    }
}
