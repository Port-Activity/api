<?php
namespace SMA\PAA\ORM;

class LogisticsTimestampPrettyModel extends LogisticsTimestampModel
{
    public $external_id;
    public $front_license_plates;
    public $rear_license_plates;
    public $containers;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function setFromLogisticsTimestamp(LogisticsTimestampModel $logisticsTimestampModel)
    {
        $this->time = $logisticsTimestampModel->time;
        $this->checkpoint = $logisticsTimestampModel->checkpoint;
        $this->direction = $logisticsTimestampModel->direction;
        $this->payload = $logisticsTimestampModel->payload;
        $this->created_by = $logisticsTimestampModel->created_by;
        $this->created_at = $logisticsTimestampModel->created_at;
        $this->modified_by = $logisticsTimestampModel->modified_by;
        $this->modified_at = $logisticsTimestampModel->modified_at;

        $decodedPayload = json_decode($this->payload, true);
        $this->external_id = $decodedPayload["external_id"];
        $this->front_license_plates = $decodedPayload["front_license_plates"];
        $this->rear_license_plates = $decodedPayload["rear_license_plates"];
        $this->containers = $decodedPayload["containers"];
    }
}
