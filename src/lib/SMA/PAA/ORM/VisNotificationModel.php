<?php
namespace SMA\PAA\ORM;

class VisNotificationModel extends OrmModel
{
    public $time;
    public $from_service_id;
    public $message_id;
    public $message_type;
    public $notification_type;
    public $subject;
    public $payload;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $time,
        string $from_service_id,
        string $message_id,
        string $message_type,
        string $notification_type,
        string $subject,
        string $payload
    ) {
        $this->time = $time;
        $this->from_service_id = $from_service_id;
        $this->message_id = $message_id;
        $this->message_type = $message_type;
        $this->notification_type = $notification_type;
        $this->subject = $subject;
        $this->payload = $payload;
    }
}
