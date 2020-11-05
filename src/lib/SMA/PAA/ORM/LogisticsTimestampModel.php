<?php
namespace SMA\PAA\ORM;

class LogisticsTimestampModel extends OrmModel
{
    public $time;
    public $checkpoint;
    public $direction;
    public $payload;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $time,
        string $checkpoint,
        string $direction,
        array $payload
    ) {
        $this->time = $time;
        $this->checkpoint = $checkpoint;
        $this->direction = $direction;
        $this->payload = json_encode($payload);
    }
}
