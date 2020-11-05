<?php
namespace SMA\PAA\DB;

interface IConnection
{
    public function query(string $sql, ...$args): IStatement;
    public function queryOne(string $sql, ...$args): ?array;
    public function getLastInsertId(): int;
    public function queryAll(string $sql, ...$args): array;
}
