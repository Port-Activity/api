<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;

class VisMessageRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if ($model->message_type !== "TXT") {
            throw new InvalidArgumentException(
                "Invalid message type: " . $model->message_type
            );
        }

        return parent::save($model, $skipCrudLog);
    }

    public function ack(string $messageId)
    {
        $model = $this->first(["message_id" => $messageId]);
        if (isset($model)) {
            $this->update("UPDATE $this->table SET ack=? WHERE id=$model->id", "TRUE");
        }
    }

    // TODO operational ack
}
