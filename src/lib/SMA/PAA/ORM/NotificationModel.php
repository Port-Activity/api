<?php
namespace SMA\PAA\ORM;

class NotificationModel extends OrmModel
{
    public $type;
    public $message;
    public $ship_imo = null;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $type,
        string $message,
        ?string $ship_imo
    ) {
        $this->type = $type;
        $this->message = $message;
        if ($ship_imo) {
            $this->ship_imo = $ship_imo;
        }
    }
}
