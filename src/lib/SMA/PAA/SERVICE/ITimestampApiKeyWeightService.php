<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\TimestampModel;

interface ITimestampApiKeyWeightService
{
    public function list(): array;
    public function modify(
        string $timestamp_time_type,
        string $timestamp_state,
        array $api_key_ids
    ): array;
    public function checkApiKeyPermission(TimestampModel $timestampModel): bool;
}
