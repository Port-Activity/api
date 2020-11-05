<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\BerthModel;

interface IBerthService
{
    public function add(string $code, string $name, $nominatable): array;
    public function update(int $id, string $code, string $name, $nominatable): array;
    public function delete(int $id): array;
    public function get(int $id): ?BerthModel;
    public function list(
        int $limit = null,
        int $offset = null,
        string $sort = null,
        string $search = null
    ): array;
}
