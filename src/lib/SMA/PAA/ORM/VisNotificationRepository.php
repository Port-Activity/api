<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\VisMessageRepository;
use SMA\PAA\ORM\VisVoyagePlanRepository;

class VisNotificationRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        $res = parent::save($model, $skipCrudLog);

        if ($model->notification_type === "ACKNOWLEDGEMENT_RECEIVED") {
            if ($model->message_type === "TXT") {
                $visMessageRepository = new VisMessageRepository();
                $visMessageRepository->ack($model->message_id);
            } elseif ($model->message_type === "RTZ") {
                $visVoyagePlanRepository = new VisVoyagePlanRepository();
                $visVoyagePlanRepository->ack($model->message_id);
            }
        }

        // TODO operational ack

        return $res;
    }

    public function getAcks(string $messageId): array
    {
        $res = [];
        $res["ack"] = false;
        $res["operational_ack"] = false;

        $ackRes = $this->first(
            ["message_id" => $messageId, "notification_type" => "ACKNOWLEDGEMENT_RECEIVED"],
            "time DESC"
        );

        if (isset($ackRes)) {
            $res["ack"] = true;
        }

        // TODO operational ack

        return $res;
    }
}
