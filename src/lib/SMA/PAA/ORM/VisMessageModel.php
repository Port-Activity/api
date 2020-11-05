<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\VisNotificationRepository;

class VisMessageModel extends OrmModel
{
    public $time;
    public $from_service_id;
    public $to_service_id;
    public $message_id;
    public $message_type;
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
        string $payload
    ) {
        $this->time = $time;
        $this->from_service_id = $from_service_id;
        $this->to_service_id = $to_service_id;
        $this->message_id = $message_id;
        $this->message_type = $message_type;
        $this->payload = $payload;
        $this->ack = "f";
        $this->operational_ack = "f";

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
