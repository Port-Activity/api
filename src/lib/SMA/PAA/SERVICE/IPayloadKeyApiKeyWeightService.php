<?php
namespace SMA\PAA\SERVICE;

interface IPayloadKeyApiKeyWeightService
{
    public function list(): array;
    public function modify(
        string $payload_key,
        array $api_key_ids
    ): array;
}
