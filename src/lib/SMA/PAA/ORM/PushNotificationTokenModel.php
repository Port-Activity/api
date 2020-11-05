<?php
namespace SMA\PAA\ORM;

class PushNotificationTokenModel extends OrmModel
{
    public $installation_id;
    public $platform;
    public $token;
    public $user_id;

    public function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function set(int $user_id, string $installation_id, string $platform, ?string $token)
    {
        $this->installation_id = $installation_id;
        $this->platform = $platform;
        $this->token = $token;
        $this->user_id = $user_id;
    }
}
