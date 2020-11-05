<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VesselModel;

class FakeVesselService implements IVesselService
{
    public function vessel(int $imo): ?VesselModel
    {
        $model = new VesselModel();
        $model->set($imo, "Fake Ship");
        return $model;
    }
}
