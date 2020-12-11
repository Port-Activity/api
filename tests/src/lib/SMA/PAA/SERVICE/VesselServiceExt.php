<?php

namespace SMA\PAA\SERVICE;

class VesselServiceExt extends VesselService
{
    public $fakeVesselRepository;
    public $fakeVesselTypeRepository;
    public $fakeStateService;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        $this->fakeVesselRepository = new FakeVesselRepository();
        $this->fakeVesselTypeRepository = new FakeVesselTypeRepository();
        $this->fakeStateService = new FakeStateService();
        parent::setVesselRepository($this->fakeVesselRepository);
        parent::setVesselTypeRepository($this->fakeVesselTypeRepository);
        parent::setStateService($this->fakeStateService);
    }
}
