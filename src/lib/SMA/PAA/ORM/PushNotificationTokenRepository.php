<?php
namespace SMA\PAA\ORM;

class PushNotificationTokenRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        $newPushtoken = $model;
        if ($model->user_id) {
            $platform = $model->platform;
            $installation_id = $model->installation_id;
            // Allow only one user to listen for push at a time per device
            $existingPushtoken = $this->getWithQuery(
                "SELECT * FROM {$this->table}"
                . " WHERE platform=? AND installation_id=?",
                $platform,
                $installation_id
            );
        }
        if ($existingPushtoken) {
            $newPushtoken->id = $existingPushtoken->id;
            $newPushtoken->created_at = $existingPushtoken->created_at;
            $newPushtoken->created_by = $existingPushtoken->created_by;
        }

        if ($newPushtoken->token) {
            return parent::save($newPushtoken, $skipCrudLog);
        } elseif ($newPushtoken->id) {
            // Allow revoking of push notification token
            return parent::delete([$newPushtoken->id], $skipCrudLog);
        }
        return 0;
    }
}
