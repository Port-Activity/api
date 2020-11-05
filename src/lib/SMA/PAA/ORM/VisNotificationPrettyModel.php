<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\VisVesselRepository;

class VisNotificationPrettyModel extends VisNotificationModel
{
    public $from_name;

    private $visVesselRepository;

    public function __construct()
    {
        $this->from_name = "";

        $this->visVesselRepository = new VisVesselRepository();

        parent::__construct(__CLASS__);
    }

    public function setFromVisNotification(VisNotificationModel $visNotificationModel)
    {
        $this->id = $visNotificationModel->id;
        $this->time = $visNotificationModel->time;
        $this->from_service_id = $visNotificationModel->from_service_id;
        $this->message_id = $visNotificationModel->message_id;
        $this->message_type = $visNotificationModel->message_type;
        $this->notification_type = $visNotificationModel->notification_type;
        $this->subject = $visNotificationModel->subject;
        $this->payload = $visNotificationModel->payload;
        $this->created_by = $visNotificationModel->created_by;
        $this->created_at = $visNotificationModel->created_at;
        $this->modified_by = $visNotificationModel->modified_by;
        $this->modified_at = $visNotificationModel->modified_at;

        $this->from_name = $this->visVesselRepository->getVesselNameWithServiceId(
            $visNotificationModel->from_service_id
        );
    }
}
