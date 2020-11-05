<?php
namespace SMA\PAA\SERVICE;

use Predis\Client;

class RedisRecentLogsClient extends Client
{
    public function __construct()
    {
        $url = getenv("REDIS_RECENT_LOGS_URL");
        if (!$url) {
            throw new \Exception("Missing env REDIS_RECENT_LOGS_URL");
        }
        parent::__construct($url);
    }
}
