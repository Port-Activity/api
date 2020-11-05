<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\Server;

class FakeServer extends Server
{
    public function bodyParameters(): array
    {
        return ["a" => 1, "b" => "c|d"];
    }
}
