<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\TimestampPrettyModel;

class PortCallHelperModel
{
    // phpcs:ignore
    const STATE_Arrival_Vessel_TrafficArea     = "Arrival_Vessel_TrafficArea";
    // phpcs:ignore
    const STATE_Arrival_Vessel_AnchorageArea   = "Arrival_Vessel_AnchorageArea";
    // phpcs:ignore
    const STATE_Arrival_Vessel_PortArea        = "Arrival_Vessel_PortArea";
    // phpcs:ignore
    const STATE_Arrival_Vessel_LOC             = "Arrival_Vessel_LOC";
    // phpcs:ignore
    const STATE_Arrival_Vessel_Berth           = "Arrival_Vessel_Berth";
    // phpcs:ignore
    const STATE_Pilotage_Commenced             = "Pilotage_Commenced";
    // phpcs:ignore
    const STATE_Pilotage_Completed             = "Pilotage_Completed";
    // phpcs:ignore
    const STATE_CargoOp_Commenced              = "CargoOp_Commenced";
    // phpcs:ignore
    const STATE_CargoOp_Completed              = "CargoOp_Completed";
    // phpcs:ignore
    const STATE_Departure_Vessel_Berth         = "Departure_Vessel_Berth";
    // phpcs:ignore
    const STATE_Departure_Vessel_LOC           = "Departure_Vessel_LOC";
    // phpcs:ignore
    const STATE_Departure_Vessel_PortArea      = "Departure_Vessel_PortArea";
    // phpcs:ignore
    const STATE_Departure_Vessel_AnchorageArea = "Departure_Vessel_AnchorageArea";
    // phpcs:ignore
    const STATE_Departure_Vessel_TrafficArea   = "Departure_Vessel_TrafficArea";

    // phpcs:ignore
    const TYPE_Actual                       = "Actual";
    // phpcs:ignore
    const TYPE_Planned                      = "Planned";
    // phpcs:ignore
    const TYPE_Estimated                    = "Estimated";
    // phpcs:ignore
    const TYPE_Recommended                  = "Recommended";

    private $data = [];
    private $departedStates = [];
    public function __construct(array $data)
    {
        $this->data = $data;


        $departedStatesEnv = getenv("PORT_CALL_DEPARTED_STATES");
        if (empty($departedStatesEnv)) {
            $this->departedStates = [self::STATE_Departure_Vessel_Berth];
        } else {
            $this->departedStates = explode(",", $departedStatesEnv);
        }
    }
    public static function fromTimeStampModel(TimestampPrettyModel $model)
    {
        return new self(json_decode(json_encode($model), true));
    }
    private function returnIfExists($key, array $array)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return null;
    }
    public function buildKey()
    {
        $payload = $this->payloadDecoded();
        $direction = is_array($payload) ? $this->returnIfExists("direction", $payload) : null;
        return $this->timeType() . "_" . $this->state() . ($direction ? "_" . $direction : "");
    }
    public function group()
    {
        return $this->returnIfExists("group", $this->data);
    }
    public function setGroup(string $group)
    {
        return $this->data["group"] = $group;
    }
    public function setSource(string $source)
    {
        return $this->data["source"] = $source;
    }
    public function getData()
    {
        $data = $this->data;
        $data["payload"] = $this->payloadDecoded();
        if (!$data["payload"]) {
            unset($data["payload"]);
        }
        return $data;
    }
    private function payloadDecoded()
    {
        $payload = $this->returnIfExists("payload", $this->data);
        // note: payload comes from test already as array, supporting not already encoded and non-encoded
        return is_array($payload) ? $payload : json_decode($payload, true);
    }
    public function payload($key)
    {
        $payloadDecoded = $this->payloadDecoded();
        return is_array($payloadDecoded) ? $this->returnIfExists($key, $payloadDecoded) : null;
    }
    public function vesselName()
    {
        return $this->returnIfExists("vessel_name", $this->data);
    }
    public function imo()
    {
        return $this->returnIfExists("imo", $this->data);
    }
    public function createdAt()
    {
        return $this->returnIfExists("created_at", $this->data);
    }
    public function time()
    {
        return $this->returnIfExists("time", $this->data);
    }
    public function state()
    {
        return $this->returnIfExists("state", $this->data);
    }
    public function stateId()
    {
        return $this->returnIfExists("state_id", $this->data);
    }
    public function timeType()
    {
        return $this->returnIfExists("time_type", $this->data);
    }
    public function timeTypeId()
    {
        return $this->returnIfExists("time_type_id", $this->data);
    }
    public function weight()
    {
        $weights = [
            self::TYPE_Actual       => 5,
            self::TYPE_Planned      => 2,
            self::TYPE_Estimated    => 1
        ];
        return $this->returnIfExists($this->state(), $weights) ?: 0;
    }
    public function isEta()
    {
        return
            $this->state() === self::STATE_Arrival_Vessel_PortArea
            && $this->timeType() === self::TYPE_Estimated
        ;
    }
    public function isAta()
    {
        return
            $this->state() === self::STATE_Arrival_Vessel_Berth
            && $this->timeType() === self::TYPE_Actual
        ;
    }
    public function isEtd()
    {
        return
            $this->state() === self::STATE_Departure_Vessel_Berth
            && $this->timeType() === self::TYPE_Estimated
        ;
    }
    public function isPtd()
    {
        return
            $this->state() === self::STATE_Departure_Vessel_Berth
            && $this->timeType() === self::TYPE_Planned
        ;
    }
    public function isAtd()
    {
        return
            $this->state() === self::STATE_Departure_Vessel_Berth
            && $this->timeType() === self::TYPE_Actual
        ;
    }
    public function createdBy()
    {
        return $this->returnIfExists("created_by", $this->data);
    }
    public function dbId()
    {
        return $this->returnIfExists("id", $this->data);
    }
    public function isAtBerth(): bool
    {
        if ($this->timeType() === self::TYPE_Actual) {
            if ($this->state() === self::STATE_Arrival_Vessel_Berth) {
                return true;
            } elseif ($this->state() === self::STATE_Pilotage_Completed &&
               $this->payload("direction") === "inbound") {
                return true;
            } elseif ($this->state() === self::STATE_CargoOp_Commenced) {
                return true;
            }
        }

        return false;
    }
    public function isDeparting(): bool
    {
        if ($this->timeType() === self::TYPE_Actual) {
            if ($this->state() === self::STATE_CargoOp_Completed) {
                return true;
            } elseif ($this->state() === self::STATE_Pilotage_Commenced &&
               $this->payload("direction") === "outbound") {
                return true;
            }
        }

        return false;
    }
    public function hasDepartedBerth(): bool
    {
        if ($this->timeType() === self::TYPE_Actual) {
            if ($this->state() === self::STATE_Departure_Vessel_Berth) {
                return true;
            }
        }

        return false;
    }
    public function hasDeparted(): bool
    {
        if ($this->timeType() === self::TYPE_Actual) {
            if (in_array($this->state(), $this->departedStates)) {
                return true;
            }
        }

        return false;
    }
}
