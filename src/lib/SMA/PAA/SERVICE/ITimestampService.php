<?php
namespace SMA\PAA\SERVICE;

interface ITimestampService
{
    public function portCallTimestamps(int $imo): array;
}
