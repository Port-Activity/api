<?php
namespace SMA\PAA\SERVICE;

class FakePortCallService
{
    public function portCallsOngoing()
    {
        $portCalls = array();
        $portCalls[] = array("ship" => array(
            "imo" => "test-imo",
            "vessel_name" => "test-vessel"
        ));

        $res = array();
        $res["portcalls"] = $portCalls;
        $res["pinned_vessels"] = array();
        return $res;
    }
}
