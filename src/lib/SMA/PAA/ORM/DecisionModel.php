<?php
namespace SMA\PAA\ORM;

use SMA\PAA\ORM\DecisionItemRepository;

class DecisionModel extends OrmModel
{
    const TYPE_PORT_CALL_DECISION = "port_call_decision";

    const STATUS_OPEN = "open";
    const STATUS_CLOSED = "closed";

    public $type;
    public $status;
    public $notification_id = null;
    public $port_call_master_id = null;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $type,
        string $status,
        int $notificationId = null,
        string $portCallMasterId = null
    ) {
        $this->type = $type;
        $this->status = $status;
        $this->notification_id = $notificationId;
        $this->port_call_master_id = $portCallMasterId;
    }

    public function getDecisionItems(): array
    {
        $res = [];

        $decisionItemRepository = new DecisionItemRepository();
        $query["decision_id"] = $this->id;

        $rawResults = $decisionItemRepository->list($query, 0, 100000);
        foreach ($rawResults as $rawResult) {
            $rawResult->response_options = json_decode($rawResult->response_options);
            $res[] = $rawResult;
        }

        return $res;
    }
}
