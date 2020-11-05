<?php
namespace SMA\PAA\SERVICE;

class FakeTransport implements ITransport
{
    public $message = "";
    public function push(String $message, int $limit): bool
    {
        $this->message = $message;
        return true;
    }
}
