<?php
namespace SMA\PAA\SERVICE;

class TransportRedis implements ITransport
{
    public function push(String $message, int $limit): bool
    {
        $client = new RedisRecentLogsClient();
        $keyName = "audit_log";
        $client->lpush($keyName, $message);
        $client->ltrim($keyName, 0, $limit);
        return true;
    }
}
