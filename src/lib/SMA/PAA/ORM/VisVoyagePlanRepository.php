<?php
namespace SMA\PAA\ORM;

use InvalidArgumentException;

use SMA\PAA\ORM\VisRtzStateRepository;

class VisVoyagePlanRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        if ($model->message_type !== "RTZ") {
            throw new InvalidArgumentException(
                "Invalid message type: " . $model->message_type
            );
        }

        return parent::save($model, $skipCrudLog);
    }

    public function ack(string $messageId)
    {
        $visRtzStateRepository = new VisRtzStateRepository();
        $rtaSentStateId = $visRtzStateRepository->mapToId("RTA_SENT");

        // We only ack sent RTA states because message ID is not unique for voyage plans
        $model = $this->first(["message_id" => $messageId, "rtz_state" => $rtaSentStateId], "time DESC");
        if (isset($model)) {
            $this->update("UPDATE $this->table SET ack=? WHERE id=$model->id", "TRUE");
        }
    }
}
