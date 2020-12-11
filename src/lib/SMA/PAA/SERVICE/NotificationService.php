<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\NotificationModel;
use SMA\PAA\ORM\NotificationRepository;
use SMA\PAA\ORM\UserModel;
use SMA\PAA\ORM\DecisionRepository;

class NotificationService
{
    const MAX_NUM_OF_DECISIONS = 5;

    private function validateDecision(string $portCallMasterId, int $imo, array $decisions)
    {
        $decisionRepository = new DecisionRepository();
        $decisionRepository->validateSimplePortCallDecision($portCallMasterId, $imo);

        if (count($decisions) > NotificationService::MAX_NUM_OF_DECISIONS) {
            throw new InvalidParameterException(
                "Too many decisions. Maximum is: " . NotificationService::MAX_NUM_OF_DECISIONS
            );
        }

        foreach ($decisions as $decision) {
            if (empty(trim($decision))) {
                throw new InvalidParameterException(
                    "Decision cannot be empty."
                );
            }
        }
    }

    private function createDecision(
        int $notificationId,
        string $portCallMasterId,
        array $decisionItemLabels
    ) {
        $decisionRepository = new DecisionRepository();
        $decisionRepository->addSimplePortCallDecision($notificationId, $portCallMasterId, $decisionItemLabels);
    }

    public function get(string $id)
    {
        $repository = new NotificationRepository();
        $model = $repository->get($id);

        if ($model !== null) {
            $model->sender = $this->getUser($model->created_by);
            if ($model->ship_imo) {
                $model->ship = $this->getVessel($model->ship_imo);
            }
            $model->decision = $model->getDecision();

            $model->children = null;
            $children = $model->getChildren();
            foreach ($children as $child) {
                $child->sender = $this->getUser($child->created_by);
                $model->children[] = $child;
            }
        }

        return $model;
    }

    public function add(
        string $type,
        string $message,
        string $ship_imo = null,
        string $port_call_master_id = null,
        array $decisions = null,
        int $parent_id = null
    ) {
        $validTypes = array(
            NotificationModel::TYPE_PORT,
            NotificationModel::TYPE_SHIP,
            NotificationModel::TYPE_PORT_CALL_DECISION
        );

        if (!$type) {
            throw new InvalidParameterException("Type can't be empty");
        } elseif (!in_array($type, $validTypes)) {
            throw new InvalidParameterException("Type should be one of there: " . implode(", ", $validTypes));
        }

        if (!$message) {
            throw new InvalidParameterException("Message can't be empty");
        }

        if (!empty($ship_imo)) {
            if (!is_numeric($ship_imo)) {
                throw new InvalidParameterException("Ship imo must be number");
            }
        }

        if ($type === NotificationModel::TYPE_PORT) {
            if (!empty($ship_imo) ||
                !empty($port_call_master_id) ||
                !empty($decisions) ||
                !empty($parent_id)) {
                throw new InvalidParameterException(
                    "Invalid assigned values for type: " . NotificationModel::TYPE_PORT
                    . ". Valid assigned value is message."
                );
            }
        } elseif ($type === NotificationModel::TYPE_SHIP) {
            if (empty($ship_imo) || !empty($port_call_master_id) || !empty($decisions)) {
                throw new InvalidParameterException(
                    "Invalid assigned values for type: " . NotificationModel::TYPE_SHIP
                    . ". Valid assigned values are message, ship_imo and parent_id."
                );
            }
        } elseif ($type === NotificationModel::TYPE_PORT_CALL_DECISION) {
            if (empty($ship_imo) ||
                empty($port_call_master_id) ||
                empty($decisions) ||
                !empty($parent_id)) {
                throw new InvalidParameterException(
                    "Invalid assigned values for type: " . NotificationModel::TYPE_PORT_CALL_DECISION
                    . ". Valid assigned values are message, ship_imo, port_call_master_id and decisions."
                );
            } else {
                $this->validateDecision($port_call_master_id, $ship_imo, $decisions);
            }
        }

        $repository = new NotificationRepository();

        if (!empty($parent_id)) {
            $parentModel = $repository->get($parent_id);

            if ($parentModel === null) {
                throw new InvalidParameterException(
                    "Invalid parent ID: " . $parent_id
                );
            }

            if ($parentModel->parent_notification_id !== null) {
                throw new InvalidParameterException(
                    "Parent ID: " . $parent_id . " already has parent. Only one level of hierarchy permitted."
                );
            }

            if ($parentModel->type !== NotificationModel::TYPE_PORT_CALL_DECISION) {
                throw new InvalidParameterException(
                    "Parent ID: " . $parent_id . " has invalid type. Only " .
                    NotificationModel::TYPE_PORT_CALL_DECISION . " allowed."
                );
            }
        }

        $id = null;
        $model = new NotificationModel();
        $model->set($type, $message, $ship_imo, $parent_id);
        $id = $repository->save($model);

        if ($type === NotificationModel::TYPE_PORT_CALL_DECISION) {
            $this->createDecision($id, $port_call_master_id, $decisions);
        }

        $notification = $this->get($id);

        $sseService = new SseService();
        $sseService->trigger('notifications', 'changed', $notification);

        $pushService = new PushNotificationService();
        $pushService->sendNotification($notification);

        return $notification;
    }

    public function delete(int $id)
    {
        $repository = new NotificationRepository();
        $model = $repository->get($id);

        if ($model === null) {
            throw new InvalidParameterException(
                "Cannot find notification with given id: " . $id
            );
        }

        $repository->delete([$id]);

        return ["result" => "OK"];
    }

    public function list()
    {
        $res = [];

        $repository = new NotificationRepository();
        $query["parent_notification_id"] = null;
        $rawResults = $repository->list($query, 0, 100, "created_at DESC");
        foreach ($rawResults as $rawResult) {
            $rawResult->sender = $this->getUser($rawResult->created_by);
            if ($rawResult->ship_imo) {
                $rawResult->ship = $this->getVessel($rawResult->ship_imo);
            }
            $rawResult->decision = $rawResult->getDecision();

            $rawResult->children = null;
            $children = $rawResult->getChildren();
            foreach ($children as $child) {
                $child->sender = $this->getUser($child->created_by);
                $rawResult->children[] = $child;
            }

            $res[] = $rawResult;
        }

        return $res;
    }

    private function getClassCached($key, $group, $callback)
    {
        if (!isset($GLOBALS[__CLASS__][$group][$key])) {
            $GLOBALS[__CLASS__][$group][$key] = call_user_func($callback);
        }
        return $GLOBALS[__CLASS__][$group][$key];
    }
    private function getUser($id)
    {
        return $this->getClassCached("user", $id, function () use ($id) {
            $service = new UserService();
            return $service->getMinimal($id);
        });
    }

    private function getVessel($imo)
    {
        return $this->getClassCached("vessel", $imo, function () use ($imo) {
            $service = new VesselService();
            return $service->getForNotification($imo);
        });
    }
}
