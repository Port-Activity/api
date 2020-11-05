<?php
namespace SMA\PAA\SERVICE;

class DateService implements IDateService
{
    public function now(): string
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }
}
