<?php
namespace SMA\PAA\ORM;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\DecisionModel;
use SMA\PAA\ORM\DecisionItemRepository;
use SMA\PAA\ORM\DecisionItemModel;
use SMA\PAA\ORM\NotificationRepository;
use SMA\PAA\ORM\PortCallRepository;

class DecisionRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    private function validateNotificationId(int $notificationId)
    {
        $notificationRepository = new NotificationRepository();
        $notificationModel = $notificationRepository->first(["id" => $notificationId]);
        if ($notificationModel === null) {
            throw new InvalidParameterException("Invalid notification ID: " . $notificationId);
        }
    }

    private function validatePortCallMasterId(string $portCallMasterId, $invalidateDone = true, int $imo = null)
    {
        $portCallRepository = new PortCallRepository();
        $portCallModel = $portCallRepository->first(["master_id" => $portCallMasterId]);
        if ($portCallModel === null) {
            throw new InvalidParameterException("Invalid port call master ID: " . $portCallMasterId);
        } elseif ($invalidateDone && $portCallModel->status === PortCallModel::STATUS_DONE) {
            throw new InvalidParameterException("Given port call is closed: " . $portCallMasterId);
        }

        if ($imo !== null) {
            if ($portCallModel->imo !== $imo) {
                throw new InvalidParameterException(
                    "IMO: " . $imo . " does not have port call master ID: " . $portCallMasterId
                );
            }
        }
    }

    public function validateSimplePortCallDecision(string $portCallMasterId, int $imo)
    {
        $this->validatePortCallMasterId($portCallMasterId, true, $imo);
    }

    public function addSimplePortCallDecision(
        int $notificationId,
        string $portCallMasterId,
        array $decisionItemLabels
    ): int {

        $this->validateNotificationId($notificationId);
        $this->validatePortCallMasterId($portCallMasterId);

        $model = new DecisionModel();
        $model->set(
            DecisionModel::TYPE_PORT_CALL_DECISION,
            DecisionModel::STATUS_OPEN,
            $notificationId,
            $portCallMasterId
        );

        $decisionId = $this->save($model);

        $responseOptionsArray = [
            DecisionItemModel::RESPONSE_NAME_ACCEPT => ["type" => DecisionItemModel::RESPONSE_TYPE_POSITIVE],
            DecisionItemModel::RESPONSE_NAME_REJECT => ["type" => DecisionItemModel::RESPONSE_TYPE_NEGATIVE]
        ];
        $responseOptions = json_encode($responseOptionsArray);

        $decisionItemRepository = new DecisionItemRepository();
        foreach ($decisionItemLabels as $decisionItemLabel) {
            $decisionItemModel = new DecisionItemModel();
            $decisionItemModel->set($decisionId, $decisionItemLabel, null, null, $responseOptions);
            $decisionItemRepository->save($decisionItemModel);
        }

        return $decisionId;
    }

    public function getDecisionForItem(
        DecisionItemModel $item
    ): ?DecisionModel {
        return $this->get($item->decision_id);
    }

    public function closeDecisionsWithPortCallMasterId(string $portCallMasterId)
    {
        $models = $this->list(["port_call_master_id" => $portCallMasterId], 0, 1000);

        foreach ($models as $model) {
            $model->status = DecisionModel::STATUS_CLOSED;
            $this->save($model);
        }
    }
}
