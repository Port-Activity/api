<?php

namespace SMA\PAA\SERVICE;

class FakeSseService
{
    public $triggeredEvents;

    public function trigger(string $category, string $event, $data): void
    {
        $this->triggeredEvents[] = [
            "category" => $category,
            "event" => $event,
            "data" => $data];
    }
}
