<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\DecisionRepository;
use SMA\PAA\ORM\DecisionModel;
use SMA\PAA\ORM\DecisionItemRepository;
use SMA\PAA\ORM\NotificationRepository;
use SMA\PAA\ORM\NotificationModel;
use SMA\PAA\SERVICE\SseService;
use SMA\PAA\SERVICE\PushNotificationService;

class DecisionService implements IDecisionService
{
    private function validateInputs(
        int $id = null,
        string $status = null
    ) {
        if ($id !== null) {
            $repository = new DecisionRepository();
            $model = $repository->first(["id" => $id]);
            if ($model === null) {
                throw new InvalidParameterException("Invalid decision ID: " . $id);
            }
        }

        if ($status !== null) {
            $validStatuses = [DecisionModel::STATUS_OPEN, DecisionModel::STATUS_CLOSED];

            if (!in_array($status, $validStatuses)) {
                throw new InvalidParameterException("Invalid decision status: " . $status);
            }
        }
    }

    public function list(
        string $status = null,
        int $notification_id = null,
        string $port_call_master_id = null,
        int $limit = null,
        int $offset = null,
        string $sort = null
    ): array {
        $this->validateInputs(null, $status);

        $res = [];

        $query = [];
        if (!empty($status)) {
            $query["status"] = $status;
        }
        if (!empty($notification_id)) {
            $query["notification_id"] = $notification_id;
        }
        if (!empty($port_call_master_id)) {
            $query["port_call_master_id"] = $port_call_master_id;
        }

        $offset = !empty($offset) ? $offset : 0;
        $limit = !empty($limit) ? $limit : 0;
        $sort = !empty($sort) ? $sort : "id";

        $repository = new DecisionRepository();
        $rawResults = $repository->listPaginated($query, $offset, $limit, $sort);

        foreach ($rawResults["data"] as $rawResult) {
            $rawResult->decision_items = $rawResult->getDecisionItems();
            $res["data"][] = $rawResult;
        }
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }

    public function get(
        int $id
    ): ?DecisionModel {
        $repository = new DecisionRepository();

        $model = $repository->get($id);

        if ($model !== null) {
            $model->decision_items = $model->getDecisionItems();
        }

        return $model;
    }

    public function close(
        int $id
    ): array {
        $this->validateInputs($id);

        $repository = new DecisionRepository();
        $model = $repository->get($id);
        $model->status = DecisionModel::STATUS_CLOSED;
        $repository->save($model);

        return ["result" => "OK"];
    }

    public function delete(
        int $id
    ): array {
        $this->validateInputs($id);

        $repository = new DecisionRepository();
        $repository->delete([$id]);

        return ["result" => "OK"];
    }

    public function setDecisionItemResponse(
        int $id,
        string $response
    ): array {
        $itemRepository = new DecisionItemRepository();
        $itemModel = $itemRepository->get($id);
        if ($itemModel === null) {
            throw new InvalidParameterException("Invalid decision item ID: " . $id);
        }

        $itemModel->setResponse($response);
        $itemRepository->save($itemModel);

        $repository = new DecisionRepository();
        $model = $repository->getDecisionForItem($itemModel);

        if ($model !== null) {
            if ($model->notification_id !== null) {
                $notificationRepository = new NotificationRepository();
                $notificationModel = $notificationRepository->get($model->notification_id);

                if ($notificationModel !== null) {
                    $responseNotification = new NotificationModel();
                    $responseNotification->type = NotificationModel::TYPE_PORT_CALL_DECISION_RESPONSE;
                    $formattedResponse = $response;
                    if ($response === "") {
                        $formattedResponse = "Response unset";
                    }
                    $responseNotification->message =
                        $formattedResponse . " for " .
                        $itemModel->label . " at " .
                        $notificationModel->message;
                    $responseNotification->ship_imo = $notificationModel->ship_imo;
                    $responseNotification->parent_notification_id = $notificationModel->id;

                    $responseNotificationId = $notificationRepository->save($responseNotification);
                    $responseNotification = $notificationRepository->get($responseNotificationId);

                    $sseService = new SseService();
                    $sseService->trigger('notifications', 'changed', $responseNotification);

                    $pushService = new PushNotificationService();
                    $pushService->sendNotification($responseNotification);
                }
            }
        }

        return ["result" => "OK"];
    }
}
