<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\VisRtzStateRepository;
use SMA\PAA\ORM\VisVesselRepository;

class VisVoyagePlanPrettyModel extends VisVoyagePlanModel
{
    public $from_name;
    public $to_name;
    public $rtz_state_name;
    public $eta;
    public $rta;
    public $eta_min;
    public $eta_max;
    public $rtz;
    public $route_name;

    private $visRtzStateRepository;
    private $visVesselRepository;

    public function __construct()
    {
        $this->from_name = "";
        $this->to_name = "";
        $this->rtz_state_name = "";
        $this->eta = "";
        $this->rta = "";
        $this->eta_min = "";
        $this->eta_max = "";
        $this->rtz = "";
        $this->route_name = "";

        $this->visRtzStateRepository = new VisRtzStateRepository();
        $this->visVesselRepository = new VisVesselRepository();

        parent::__construct(__CLASS__);
    }

    public function setFromVisVoyagePlan(VisVoyagePlanModel $visVoyagePlanModel)
    {
        $this->id = $visVoyagePlanModel->id;
        $this->time = $visVoyagePlanModel->time;
        $this->from_service_id = $visVoyagePlanModel->from_service_id;
        $this->to_service_id = $visVoyagePlanModel->to_service_id;
        $this->message_id = $visVoyagePlanModel->message_id;
        $this->message_type = $visVoyagePlanModel->message_type;
        $this->payload = $visVoyagePlanModel->payload;
        $this->ack = $visVoyagePlanModel->ack;
        $this->operational_ack = $visVoyagePlanModel->operational_ack;
        $this->created_by = $visVoyagePlanModel->created_by;
        $this->created_at = $visVoyagePlanModel->created_at;
        $this->modified_by = $visVoyagePlanModel->modified_by;
        $this->modified_at = $visVoyagePlanModel->modified_at;

        $this->from_name = $this->visVesselRepository->getVesselNameWithServiceId($visVoyagePlanModel->from_service_id);
        $this->to_name = $this->visVesselRepository->getVesselNameWithServiceId($visVoyagePlanModel->to_service_id);
        $this->rtz_state_name = $this->visRtzStateRepository->getStateNameWithStateId($visVoyagePlanModel->rtz_state);

        $payloadArray = json_decode($visVoyagePlanModel->payload, true);

        $hasRtz = true;
        if (!isset($payloadArray["stmMessage"])) {
            $hasRtz = false;
        }
        if (!isset($payloadArray["stmMessage"]["message"])) {
            $hasRtz = false;
        }
        if ($hasRtz) {
            $this->rtz = $payloadArray["stmMessage"]["message"];
        }

        $rtzParseResults = json_decode($visVoyagePlanModel->rtz_parse_results, true);
        if (isset($rtzParseResults["eta"])) {
            $this->eta = $rtzParseResults["eta"];
        }
        if (isset($rtzParseResults["route_name"])) {
            $this->route_name = $rtzParseResults["route_name"];
        }

        if (isset($payloadArray["rta"])) {
            $this->rta = $payloadArray["rta"];
        }
        if (isset($payloadArray["eta_min"])) {
            $this->eta_min = $payloadArray["eta_min"];
        }
        if (isset($payloadArray["eta_max"])) {
            $this->eta_max = $payloadArray["eta_max"];
        }
    }
}
