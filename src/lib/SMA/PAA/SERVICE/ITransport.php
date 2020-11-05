<?php
namespace SMA\PAA\SERVICE;

interface ITransport
{
    public function push(String $message, int $limit): bool;
}
