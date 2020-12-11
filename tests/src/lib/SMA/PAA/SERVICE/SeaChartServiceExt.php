<?php

namespace SMA\PAA\SERVICE;

class SeaChartServiceExt extends SeaChartService
{
    public $fakeVesselLocationRepository;
    public $fakeStateService;
    public $fakeSseService;
    public $fakeFixedVesselRepository;
    public $fakeVesselTypeRepository;

    public function __construct()
    {
        parent::__construct(null);
        $this->fakeVesselLocationRepository = new FakeSeaChartVesselLocationRepository();
        $this->fakeStateService = new FakeStateService;
        $this->fakeSseService = new FakeSseService();
        $this->fakeFixedVesselRepository = new FakeSeaChartFixedVesselRepository();
        $this->fakeVesselTypeRepository = new FakeVesselTypeRepository();
        parent::setFixedVesselRepository($this->fakeFixedVesselRepository);
        parent::setVesselLocationRepository($this->fakeVesselLocationRepository);
        parent::setMarkerTypeRepository(new FakeSeaChartMarkerTypeRepository());
        parent::setVesselRepository(new FakeVesselRepository());
        parent::setSseService($this->fakeSseService);
        parent::setStateService($this->fakeStateService);
        parent::setVesselTypeRepository($this->fakeVesselTypeRepository);
    }
}
