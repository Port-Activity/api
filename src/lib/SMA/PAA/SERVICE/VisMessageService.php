<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\VisRtzStateModel;
use SMA\PAA\ORM\VisVoyagePlanModel;
use SMA\PAA\ORM\VisVoyagePlanPrettyModel;

class VisMessageService
{
    private function returnIfExists($key, $array)
    {
        return array_key_exists($key, $array) ? $array[$key] : null;
    }

    private function fillTemplate($string)
    {
        // <port> = Port of Gävle
        // <port unlocode> = SEGVX
        // <location> = “pilot boarding area” or “outer_port_area”

        $map = [
            "<port>"            => getenv("VIS_PORT_NAME")
            ,"<port unlocode>"  => getenv("VIS_PORT_UNLOCODE")
            ,"<location name>"  => getenv("RTA_POINT_LOCATION_NAME")
        ];
        return str_replace(array_keys($map), $map, $string);
    }
    private function shareYourVoyagePlan()
    {
        return
            "Share your voyage plan with <port> (<port unlocode>). "
            . "Voyage plan should have calculated schedule and waypoint near <location name>.";
    }
    private function calculatedScheduleNotFound(string $routeName)
    {
        return
            "The voyage plan (" . $routeName . ") does not have a valid schedule. "
            . "Please send an updated voyage plan with a valid ETA for waypoint near <location name>.";
    }

    private function syncNotFoundCanNotBeAdded(string $routeName)
    {
        return
            "The voyage plan (" . $routeName . ") does not have waypoint near <location name>. "
            . "Please send an updated voyage plan with a valid ETA for waypoint near <location name>.";
    }

    public function automaticMessageForNoVoyagePlan()
    {
        return $this->fillTemplate($this->shareYourVoyagePlan());
    }

    public function automaticMessageForVisStatusIfAny(VisVoyagePlanModel $visVoyagePlanModel)
    {
        $visVoyagePlanPrettyModel = new VisVoyagePlanPrettyModel($visVoyagePlanModel);
        $visVoyagePlanPrettyModel->setFromVisVoyagePlan($visVoyagePlanModel);
        $routeName = $visVoyagePlanPrettyModel->route_name;
        $routeName = empty($routeName) ? "UNKNOWN" : $routeName;
        $map = [
            VisRtzStateModel::CALCULATED_SCHEDULE_NOT_FOUND     => $this->calculatedScheduleNotFound($routeName)
            ,VisRtzStateModel::SYNC_NOT_FOUND_CAN_NOT_BE_ADDED  => $this->syncNotFoundCanNotBeAdded($routeName)
        ];
        return $this->fillTemplate($this->returnIfExists($visVoyagePlanModel->rtz_state, $map));
    }

    public function getSyncPointAreaString(): string
    {
        $lat = getenv("VIS_SYNC_POINT_LAT");
        $lon = getenv("VIS_SYNC_POINT_LON");
        $radius = getenv("VIS_SYNC_POINT_RADIUS");

        return ""
        . "<Circle>"
        . "<position"
        . " lat=\"". $lat . "\""
        . " lon=\"". $lon . "\"/"
        . ">"
        . "<radius>" . $radius . "</radius>"
        . "</Circle>";
    }
}
