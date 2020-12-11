<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\DecisionModel;

interface IDecisionService
{
    public function list(
        string $status = null,
        int $notification_id = null,
        string $port_call_master_id = null,
        int $limit = null,
        int $offset = null,
        string $sort = null
    ): array;
    public function get(int $id): ?DecisionModel;
    public function close(int $id): array;
    public function delete(int $id): array;
    public function setDecisionItemResponse(int $id, string $response): array;
}
