<?php
namespace SMA\PAA\ORM;

class PortCallModel extends OrmModel
{
    const STATUS_ARRIVING       = "arriving"; // eta ata etd
    const STATUS_AT_BERTH       = "at berth"; // ops etd
    const STATUS_DEPARTING      = "departing";
    const STATUS_DEPARTED_BERTH = "departed berth"; // atd berth
    const STATUS_DEPARTED       = "departed"; // atd port area
    const STATUS_DONE           = "done";     // no more on timeline

    const NEXT_EVENT_ETA        = "ETA";
    const NEXT_EVENT_ETD        = "ETD";
    const NEXT_EVENT_ATD        = "ATD";

    public $status;                 // master status of port call

    public $imo;
    public $vessel_name;
    public $from_port;              // port where vessel is coming from
    public $to_port;                // port where vessel is going to
    public $next_port;              // next port when leaving (note: not known yet)
    public $mmsi;
    public $call_sign;
    public $net_weight;
    public $gross_weight;
    public $loa;
    public $beam;
    public $draft;
    public $nationality;

    public $first_eta;              // first estimated time of arrival
    public $first_etd;              // first estimated time of arrival
    public $current_eta;            // estimated time of arrival
    public $current_etd;            // estimated time of departure
    public $current_pta;            // planned time of arrival, only valid after rta
    public $current_ptd;            // planned time of departire, only valid after rta
    public $rta;                    // recommended time of arrival (slot time), given by port authorities
    public $rta_details;            // recommended time of arrival details
    private $rta_details_array;
    public $ata;                    // actual time of arrival
    public $atd;                    // actual time of departure

    public $is_vis;                 // is vessel is stm vessel and is vis enabled

    public $inbound_piloting_status;
    public $outbound_piloting_status;
    public $cargo_operations_status;

    public $next_event_title;
    public $next_event_ts;

    public $berth;
    public $eta_form_email;

    public $live_eta;
    public $live_eta_details;

    public $voyage_plan_request;

    private $live_eta_details_array = null;

    public $berth_name;

    public $slot_reservation_id;
    public $slot_reservation_status;
    public $rta_window_start;
    public $rta_window_end;
    public $laytime;

    public $master_id;
    public $master_start;
    public $master_end;
    public $master_manual = "f";

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        int $imo,
        string $status,
        string $fromPortUnlocode = null,
        string $toPortUnlocode = null
    ) {
        $this->imo = $imo;
        $this->status = $status;
        $this->from_port_unlocode = $fromPortUnlocode;
        $this->to_port_unlocode = $toPortUnlocode;
    }
    public function clear()
    {
        $this->status = PortCallModel::STATUS_ARRIVING;
        // imo not nulled
        $this->vessel_name = null;
        $this->from_port = null;
        $this->to_port = null;
        $this->next_port = null;
        $this->mmsi = null;
        $this->call_sign = null;
        $this->net_weight = null;
        $this->gross_weight = null;
        $this->loa = null;
        $this->beam = null;
        $this->draft = null;
        $this->nationality = null;

        // first_eta not nulled
        $this->first_etd = null;
        // current_eta not nulled
        $this->current_etd = null;
        $this->current_pta = null;
        $this->current_ptd = null;
        $this->rta = null;
        $this->rta_details = null;
        $this->rta_details_array = [];
        $this->ata = null;
        $this->atd = null;

        $this->is_vis = null; // if nulled may trigger vis text message

        $this->inbound_piloting_status = null;
        $this->outbound_piloting_status = null;
        $this->cargo_operations_status = null;

        $this->next_event_title = null;
        $this->next_event_ts = null;

        $this->berth = null;
        $this->eta_form_email = null;

        // live_eta not nulled
        // live_eta_details not nulled

        // live_eta_details_array not nulled

        $this->berth_name = null;

        // voyage_plan_request not nulled

        // slot_reservation_id not nulled
        // slot_reservation_status not nulled
        // rta_window_start not nulled
        // rta_window_end not nulled
        // laytime not nulled

        // master_id not nulled
        // master_start not nulled
        // master_end not nulled
        // master_manual not nulled
    }
    public function rta(string $key)
    {
        if (!$this->rta_details_array) {
            $this->rta_details_array = json_decode($this->rta_details, true) ?: [];
        }
        return array_key_exists($key, $this->rta_details_array) ? $this->rta_details_array[$key] : null;
    }
    public function liveEta(string $key)
    {
        if (!$this->live_eta_details_array) {
            $this->live_eta_details_array = json_decode($this->live_eta_details, true) ?: [];
        }
        return array_key_exists($key, $this->live_eta_details_array) ? $this->live_eta_details_array[$key] : null;
    }
    public function setIsVis($value)
    {
        $this->is_vis = $value ? "t" : "f";
    }
    public function getIsVis(): bool
    {
        return $this->is_vis === "t" || $this->is_vis === true;
    }
    public function setMasterManual($value)
    {
        $this->master_manual = $value ? "t" : "f";
    }
    public function getMasterManual(): bool
    {
        return $this->master_manual === "t" || $this->master_manual === true;
    }
}
