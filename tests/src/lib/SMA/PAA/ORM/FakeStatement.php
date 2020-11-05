<?php
namespace SMA\PAA\ORM;

use SMA\PAA\DB\IStatement;

class FakeStatement implements IStatement
{
    public function fetchAll()
    {
        return [];
    }
    public function fetch()
    {
        return null;
    }
}
