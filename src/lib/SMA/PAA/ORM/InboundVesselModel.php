<?php
namespace SMA\PAA\ORM;

class InboundVesselModel extends OrmModel
{
    public $time;
    public $imo;
    public $vessel_name;
    public $from_service_id;
    public $from_service_name;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $time,
        int $imo,
        string $vesselName,
        string $fromServiceId
    ) {
        $this->time = $time;
        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->from_service_id = $fromServiceId;

        $portRepository = new PortRepository();
        $this->from_service_name = $portRepository->getNameWithServiceId($this->from_service_id);

        if (empty($this->from_service_name)) {
            $this->from_service_name = $this->from_service_id;
        }
    }
}
