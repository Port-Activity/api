<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\NotificationRepository;
use SMA\PAA\ORM\DecisionRepository;
use SMA\PAA\ORM\DecisionModel;

class NotificationModel extends OrmModel
{
    const TYPE_PORT = "port";
    const TYPE_SHIP = "ship";
    const TYPE_PORT_CALL_DECISION = "port_call_decision";
    const TYPE_PORT_CALL_DECISION_RESPONSE = "port_call_decision_response";

    public $type;
    public $message;
    public $ship_imo = null;
    public $parent_notification_id = null;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $type,
        string $message,
        string $ship_imo = null,
        int $parent_notification_id = null
    ) {
        $this->type = $type;
        $this->message = $message;
        if ($ship_imo) {
            $this->ship_imo = $ship_imo;
        }
        if ($parent_notification_id) {
            $this->parent_notification_id = $parent_notification_id;
        }
    }

    public function getChildren(): array
    {
        $res = [];

        $repository = new NotificationRepository();
        $query["parent_notification_id"] = $this->id;
        $res = $repository->list($query, 0, 10000, "created_at DESC");

        return $res;
    }

    public function getDecision(): ?DecisionModel
    {
        $res = null;

        $decisionRepository = new DecisionRepository();
        $res = $decisionRepository->first(["notification_id" => $this->id]);

        if ($res !== null) {
            $res->decision_items = $res->getDecisionItems();
        }

        return $res;
    }
}
