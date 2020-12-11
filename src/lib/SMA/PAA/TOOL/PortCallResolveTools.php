<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\ORM\TimestampModel;
use SMA\PAA\ORM\TimestampPrettyModel;
use SMA\PAA\ORM\PortCallModel;
use SMA\PAA\SERVICE\PortCallHelperModel;

class PortCallResolveTools
{
    const ORDER_ARRIVING = 1;
    const ORDER_AT_BERTH = 2;
    const ORDER_DEPARTING = 3;
    const ORDER_DEPARTED = 4;
    const ORDER_DONE = 5;

    private function getPortCallOrder(PortCallModel $portCallModel): int
    {
        if ($portCallModel->status === PortCallModel::STATUS_ARRIVING) {
            return PortCallResolveTools::ORDER_ARRIVING;
        } elseif ($portCallModel->status === PortCallModel::STATUS_AT_BERTH) {
            return PortCallResolveTools::ORDER_AT_BERTH;
        } elseif ($portCallModel->status === PortCallModel::STATUS_DEPARTING) {
            return PortCallResolveTools::ORDER_DEPARTING;
        } elseif ($portCallModel->status === PortCallModel::STATUS_DEPARTED_BERTH ||
                  $portCallModel->status === PortCallModel::STATUS_DEPARTED) {
            return PortCallResolveTools::ORDER_DEPARTED;
        } elseif ($portCallModel->status === PortCallModel::STATUS_DONE) {
            return PortCallResolveTools::ORDER_DONE;
        }
    }

    private function getTimestampOrder(TimestampModel $timestampModel): ?int
    {
        $p = null;
        if (!empty($timestampModel->payload)) {
            $p = json_decode($timestampModel->payload);
        }
        $d = null;
        if (isset($p->direction)) {
            $d = $p->direction;
        }

        $timestampPrettyModel = new TimestampPrettyModel();
        $timestampPrettyModel->setFromTimestamp($timestampModel);

        $t = $timestampPrettyModel->time_type;
        $s = $timestampPrettyModel->state;
        $ts = $t . $s;

        // TODO: STATE_Arrival_Vessel_AnchorageArea cannot be ordered until we know direction
        // TODO: STATE_Arrival_Vessel_LOC cannot be ordered until we know location and direction
        // TODO: STATE_Departure_Vessel_AnchorageArea cannot be ordered until we know direction
        // TODO: STATE_Departure_Vessel_LOC cannot be ordered until we know location and direction
        if ($s === PortCallHelperModel::STATE_Arrival_Vessel_TrafficArea ||
           $s === PortCallHelperModel::STATE_Arrival_Vessel_PortArea) {
            return PortCallResolveTools::ORDER_ARRIVING;
        } elseif ($s === PortCallHelperModel::STATE_Arrival_Vessel_Berth) {
            if ($t === PortCallHelperModel::TYPE_Actual) {
                return PortCallResolveTools::ORDER_AT_BERTH;
            } else {
                return PortCallResolveTools::ORDER_ARRIVING;
            }
        } elseif ($s === PortCallHelperModel::STATE_Pilotage_Commenced) {
            if ($d === "inbound") {
                return PortCallResolveTools::ORDER_ARRIVING;
            } elseif ($d === "outbound") {
                if ($t === PortCallHelperModel::TYPE_Actual) {
                    return PortCallResolveTools::ORDER_DEPARTING;
                } else {
                    return PortCallResolveTools::ORDER_AT_BERTH;
                }
            }
        } elseif ($s === PortCallHelperModel::STATE_Pilotage_Completed) {
            if ($d === "inbound") {
                if ($t === PortCallHelperModel::TYPE_Actual) {
                    return PortCallResolveTools::ORDER_AT_BERTH;
                } else {
                    return PortCallResolveTools::ORDER_ARRIVING;
                }
            } elseif ($d === "outbound") {
                if ($t === PortCallHelperModel::TYPE_Actual) {
                    return PortCallResolveTools::ORDER_DEPARTED;
                } else {
                    return PortCallResolveTools::ORDER_AT_BERTH;
                }
            }
        } elseif ($s === PortCallHelperModel::STATE_CargoOp_Commenced) {
            return PortCallResolveTools::ORDER_AT_BERTH;
        } elseif ($s === PortCallHelperModel::STATE_CargoOp_Completed) {
            if ($t === PortCallHelperModel::TYPE_Actual) {
                return PortCallResolveTools::ORDER_DEPARTING;
            } else {
                return PortCallResolveTools::ORDER_AT_BERTH;
            }
        } elseif ($s === PortCallHelperModel::STATE_Departure_Vessel_Berth ||
                 $s === PortCallHelperModel::STATE_Departure_Vessel_PortArea ||
                 $s === PortCallHelperModel::STATE_Departure_Vessel_TrafficArea) {
            if ($t === PortCallHelperModel::TYPE_Actual) {
                return PortCallResolveTools::ORDER_DEPARTED;
            } else {
                return PortCallResolveTools::ORDER_DEPARTING;
            }
        }

        error_log("Cannot get timestamp order for: " . $ts);

        return null;
    }

    public function resolvePortCallForTimestamp(TimestampModel $timestampModel, array $portCallModels): PortCallModel
    {
        $res = null;

        $timestampOrder = $this->getTimestampOrder($timestampModel);
        if ($timestampOrder === null) {
            return end($portCallModels);
        }

        $diff = PHP_INT_MAX;
        foreach ($portCallModels as $portCallModel) {
            $portCallOrder = $this->getPortCallOrder($portCallModel);
            $newDiff = abs($timestampOrder - $portCallOrder);
            if ($newDiff <= $diff) {
                $res = $portCallModel;
                $diff = $newDiff;
            }
        }

        return $res;
    }
}
