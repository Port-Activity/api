<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\NotificationModel;
use SMA\PAA\ORM\NotificationRepository;
use SMA\PAA\ORM\UserModel;

class NotificationService
{
    public function get(string $id)
    {
        $repository = new NotificationRepository();
        $model = $repository->get($id);
        $model->sender = $this->getUser($model->created_by);
        if ($model->ship_imo) {
            $model->ship = $this->getVessel($model->ship_imo);
        }
        if ($model->sender) {
            unset($model->sender->last_login_time);
        }
        if ($model->sender) {
            unset($model->sender->last_login_data);
        }
        if ($model->sender) {
            unset($model->sender->last_session_time);
        }
        if ($model->sender) {
            unset($model->sender->registration_code_id);
        }
        unset($model->created_by);
        unset($model->modified_at);
        unset($model->modified_by);
        return $model;
    }

    public function add(string $type, string $message, string $ship_imo = null)
    {
        $validTypes = array("port", "ship");
        if (!$type) {
            throw new InvalidParameterException("Type can't be empty");
        } elseif (!in_array($type, $validTypes)) {
            throw new InvalidParameterException("Type should be one of there: " . implode(", ", $validTypes));
        }
        if (!$message) {
            throw new InvalidParameterException("Message can't be empty");
        }
        if ($type === "port" && $ship_imo) {
            throw new InvalidParameterException("Ship imo can't be assigned when message type is 'port'");
        }
        if ($type === "ship" && !is_numeric($ship_imo)) {
            throw new InvalidParameterException("Ship imo must be number");
        }

        $id = null;
        $repository = new NotificationRepository();
        $model = new NotificationModel();
        $model->set($type, $message, $ship_imo);
        $id = $repository->save($model);

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
        return $repository->delete([$id]);
    }

    public function list()
    {
        $repository = new NotificationRepository();
        return array_map(function ($object) {
            $object->sender = $this->getUser($object->created_by);
            if ($object->ship_imo) {
                $object->ship = $this->getVessel($object->ship_imo);
            }
            if ($object->sender) {
                unset($object->sender->last_login_time);
                unset($object->sender->last_login_data);
                unset($object->sender->last_session_time);
                unset($object->sender->registration_code_id);

                if ($object->sender->status === UserModel::STATUS_DELETED) {
                    $object->sender->email = "ex-user";
                }
            }
            unset($object->created_by);
            unset($object->modified_at);
            unset($object->modified_by);
            return $object;
        }, $repository->list([], 0, 100, "created_at DESC"));
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
            return $service->vessel($imo);
        });
    }
}
