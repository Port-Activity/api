<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\VisRtzStateRepository;
use SMA\PAA\ORM\VisNotificationRepository;

class VisVoyagePlanModel extends OrmModel
{
    public $time;
    public $from_service_id;
    public $to_service_id;
    public $message_id;
    public $message_type;
    public $rtz_state;
    public $rtz_parse_results;
    public $payload;
    public $ack;
    public $operational_ack;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $time,
        string $from_service_id,
        string $to_service_id,
        string $message_id,
        string $message_type,
        string $rtz_state,
        string $rtz_parse_results,
        string $payload
    ) {
        $visRtzStateRepository = new VisRtzStateRepository();

        $this->time = $time;
        $this->from_service_id = $from_service_id;
        $this->to_service_id = $to_service_id;
        $this->message_id = $message_id;
        $this->message_type = $message_type;
        $this->rtz_state = $visRtzStateRepository->mapToId($rtz_state);
        $this->rtz_parse_results = $rtz_parse_results;
        $this->payload = $payload;
        $this->ack = "f";
        $this->operational_ack = "f";

        // We only ack sent RTA states because message ID is not unique for voyage plans
        if ($rtz_state === "RTA_SENT") {
            $visNotificationRepository = new VisNotificationRepository();
            $acks = $visNotificationRepository->getAcks($this->message_id);
            if ($acks["ack"] === true) {
                $this->ack = "t";
            }
            if ($acks["operational_ack"] === true) {
                $this->operational_ack = "t";
            }
        }
    }
}
