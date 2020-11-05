<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VesselModel;

interface IVesselService
{
    public function vessel(int $imo): ?VesselModel;
}
