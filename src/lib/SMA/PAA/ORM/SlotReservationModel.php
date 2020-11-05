<?php
namespace SMA\PAA\ORM;

class SlotReservationModel extends OrmModel
{
    public $nomination_id;
    public $berth_id;
    public $email;
    public $imo;
    public $vessel_name;
    public $loa;
    public $beam;
    public $draft;
    public $laytime;
    public $eta;
    public $rta_window_start;
    public $rta_window_end;
    public $jit_eta;
    public $slot_reservation_status_id;
    public $port_call_id;
    public $jit_eta_discrepancy_time;
    public $jit_eta_alert_state;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $nominationId = null,
        int $berthId = null,
        string $email,
        int $imo,
        string $vesselName,
        float $loa,
        float $beam,
        float $draft,
        string $laytime,
        string $eta,
        string $rtaWindowStart = null,
        string $rtaWindowEnd = null,
        string $jitEta = null,
        int $slotReservationStatusId,
        int $portCallId = null,
        string $jitEtaDiscrepancyTime = null,
        string $jitEtaAlertState = "green"
    ) {
        $this->nomination_id = $nominationId;
        $this->berth_id = $berthId;
        $this->email = $email;
        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->loa = $loa;
        $this->beam = $beam;
        $this->draft = $draft;
        $this->laytime = $laytime;
        $this->eta = $eta;
        $this->rta_window_start = $rtaWindowStart;
        $this->rta_window_end = $rtaWindowEnd;
        $this->jit_eta = $jitEta;
        $this->slot_reservation_status_id = $slotReservationStatusId;
        $this->port_call_id = $portCallId;
        $this->jit_eta_discrepancy_time = $jitEtaDiscrepancyTime;
        $this->jit_eta_alert_state = $jitEtaAlertState;
    }
}
