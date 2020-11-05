<?php

namespace SMA\PAA\SERVICE;

class SeaChartServiceExt extends SeaChartService
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
        parent::setPortCallService(new FakePortCallService);
        parent::setStateService(new FakeStateService);
    }
}
