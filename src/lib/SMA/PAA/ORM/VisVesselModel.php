<?php
namespace SMA\PAA\ORM;

class VisVesselModel extends OrmModel
{
    public $imo;
    public $vessel_name;
    public $service_id;
    public $service_url;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $imo,
        string $vesselName,
        string $serviceId,
        string $serviceUrl
    ) {
        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->service_id = $serviceId;
        $this->service_url = $serviceUrl;
    }
}
