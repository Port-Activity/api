<?php
namespace SMA\PAA\ORM;

use SMA\PAA\DB\IConnection;
use SMA\PAA\DB\IStatement;

class FakeConnection implements IConnection
{
    private $result = ["id" => 111, "foo" => "bar"];
    private $last_args;
    public function __construct($result = null)
    {
        if ($result !== null) {
            $this->result = $result;
        }
    }
    public function query(string $sql, ...$args): IStatement
    {
        $this->last_args = func_get_args();

        # Some extra logic needed for fake IMO saving
        # Need to set valid ID after inserting vessel with 0 IMO
        # But before insertion the result array must be empty
        $last = $this->last_args;
        if (json_encode($last[0]) === '["INSERT INTO public.vessel '
            . '(created_at,created_by,modified_at,modified_by,'
            . 'imo,vessel_name) '
            . 'VALUES (?,?,?,?,?,?)"'
        ) {
                $this->result = ["id" => 234];
        }
        return new FakeStatement();
    }
    public function queryOne(string $sql, ...$args): ?array
    {
        $this->last_args = func_get_args();
        return $this->result;
    }
    public function queryAll(string $sql, ...$args): array
    {
        $this->last_args = func_get_args();
        return $this->result;
    }
    public function lastQuery()
    {
        return $this->last_args;
    }
    public function getLastInsertId(): int
    {
        return 123;
    }
}
