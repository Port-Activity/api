<?php
namespace SMA\PAA\SERVICE;

interface IStateService
{
    // Note: there is relation from this key in agent-fenix so don't change this
    // Note: this key should always be available for fenix agent
    const LATEST_PORT_CALL_IMOS                = "port_call_imos.latest";
     
    // Note: this key should always be available for agent-shiplog
    const LATEST_PORT_CALL_STATUSES            = "port_call_statuses.latest";

    const LATEST_PORT_CALLS                    = "port_calls.latest";
    const LATEST_LOGISTICS                     = "logistics.latest";
    const PINNED_VESSELS                       = "pinned_vessels";
    const LATEST_PORT_CALLS_LOCKED             = "port_calls.latest_locked";
    // Note: this key should always be available for agent-ais-griegconnect
    //       and agent-ais-digitraffic
    const SEA_CHART_FIXED_VESSELS_MMSI_IMO_MAP = "sea_chart_fixed_vessels_mmsi_imo_map";
    const LATEST_PORT_CALL_DETAILS             = "port_call_details.latest";
    const LATEST_SEA_CHART_VESSELS_AND_MARKERS = "sea_chart_vessels_markers.latest";

    public function get(string $key);
    public function getSet(string $key, callable $callback);
    public function delete(string $key);
}
