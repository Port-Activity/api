<?php
namespace SMA\PAA\SERVICE;

class FakeDateService implements IDateService
{
    public function now(): string
    {
        return "2019-10-24T23:10:18Z";
    }
}
