<?php
namespace SMA\PAA\ORM;

class NominationModel extends OrmModel
{
    public $company_name;
    public $email;
    public $imo;
    public $vessel_name;
    public $nomination_status_id;
    public $window_start;
    public $window_end;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(
        string $companyName,
        string $email,
        int $imo,
        string $vesselName,
        int $nominationStatusId,
        string $windowStart,
        string $windowEnd
    ) {
        $this->company_name = $companyName;
        $this->email = $email;
        $this->imo = $imo;
        $this->vessel_name = $vesselName;
        $this->nomination_status_id = $nominationStatusId;
        $this->window_start = $windowStart;
        $this->window_end = $windowEnd;
    }
}
