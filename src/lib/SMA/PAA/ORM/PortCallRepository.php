<?php
namespace SMA\PAA\ORM;

use SMA\PAA\SERVICE\PortCallHelperModel;
use SMA\PAA\SERVICE\StateService;
use SMA\PAA\SERVICE\SlotReservationService;

class PortCallRepository extends OrmRepository
{
    public function __construct()
    {
        parent::__construct(__CLASS__);
    }
    public function historyPortCalls(int $limit)
    {
        return $this->list(["status" => PortCallModel::STATUS_DONE], 0, $limit, "atd DESC");
    }
    public function portCallsByImo(int $imo, int $limit)
    {
        return $this->list(["imo" => $imo], 0, $limit, "atd");
    }
    public function ongoingPortCalls()
    {
        return $this->getMultipleWithQuery(
            <<<EOL
            SELECT * FROM $this->table
            WHERE
            status <> ?
            OR (
                status = ? AND atd > now() - interval '2 hours'
            )
            ORDER BY current_eta
EOL,
            PortCallModel::STATUS_DONE,
            PortCallModel::STATUS_DONE
        );
    }
    public function portCallForTimestamp(PortCallHelperModel $helperModel): ?PortCallModel
    {
        return $this->getWithQuery(
            <<<EOL
            SELECT * FROM $this->table t
            WHERE imo=?
            AND (
                status <> ?
                OR (
                    t.current_eta - interval '12 hours' < ?
                    AND ? < t.current_etd + interval '12 hours'
                )
                OR (
                    t.first_eta - interval '12 hours' < ?
                    AND ? < t.first_etd + interval '12 hours'
                )
            )
EOL,
            $helperModel->imo(),
            PortCallModel::STATUS_DONE,
            $helperModel->time(),
            $helperModel->time(),
            $helperModel->time(),
            $helperModel->time(),
        );
    }
    public function arrivalPortCallsForImo(int $imo)
    {
        return $this->getMultipleWithQuery(
            <<<EOL
            SELECT * FROM $this->table
            WHERE
            imo = ?
            AND status = ?
            ORDER BY current_eta
EOL,
            $imo,
            PortCallModel::STATUS_ARRIVING
        );
    }
    public function setLiveEta(int $imo, string $time, array $details)
    {
        $model = $this->getWithQuery(
            <<<EOL
            SELECT * FROM $this->table
            WHERE
            status <> ?
            AND imo = ?
            ORDER BY current_eta
EOL,
            PortCallModel::STATUS_DONE,
            $imo
        );

        if (!$model) {
            return false;
        }

        $this->update(
            "UPDATE $this->table SET live_eta=?,live_eta_details=? WHERE id=?",
            $time,
            json_encode($details),
            $model->id
        );

        if ($model->slot_reservation_id !== null) {
            $slotReservationService = new SlotReservationService();
            $slotReservationService->checkLiveEta($model->slot_reservation_id, $time);
        }

        return true;
    }
    public function update($sql, ...$args)
    {
        $data = parent::updateWithoutCrudHistoryStoring($sql, ...$args);
        $this->deleteRelatedStates();
        return $data;
    }
    public function save(OrmModel $model, bool $skipCrudLog = false)
    {
        $data = parent::save($model, $skipCrudLog);
        $this->deleteRelatedStates();
        return $data;
    }
    private function deleteRelatedStates()
    {
        $service = new StateService();
        $service->triggerPortCalls();
    }
    public function deletePortCallsByImo(int $imo)
    {
        $data = $this->deleteAll(["imo" => $imo]);
        $this->deleteRelatedStates();
        return $data;
    }
    public function deletePortCallById(int $id)
    {
        $data = $this->delete([$id]);
        $this->deleteRelatedStates();
        return $data;
    }
    public function saveRta(int $id, string $rta, array $rtaDetails)
    {
        $this->update(
            "UPDATE $this->table SET rta=?, rta_details=? WHERE id=?",
            $rta,
            json_encode($rtaDetails),
            $id
        );
    }
}
