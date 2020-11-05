<?php
namespace SMA\PAA\SERVICE;

class SseService
{
    public function trigger(string $category, string $event, $data): void
    {
        $redis = new RedisClient();
        $redis->publish(
            "sse",
            json_encode([
                "event" => $category . "-" . $event,
                "data" => $data,
                "id" => time() . "-" . uniqid()
            ])
        );
    }
}
