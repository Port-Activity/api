<?php
namespace SMA\PAA\ORM;

class SlotReservationRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function updatePortCallId(int $slotReservationId, int $portCallId)
    {
        $model = $this->get($slotReservationId);

        if ($model !== null) {
            if ($model->port_call_id !== $portCallId) {
                $model->port_call_id = $portCallId;
                $this->save($model);
            }
        }
    }
}
