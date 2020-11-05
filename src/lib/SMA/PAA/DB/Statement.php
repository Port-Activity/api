<?php
namespace SMA\PAA\DB;

use PDOStatement;

class Statement implements IStatement
{
    private $pdoStatement;
    public function __construct(PDOStatement $pdoStatement)
    {
        $this->pdoStatement = $pdoStatement;
    }
    public function fetchAll()
    {
        return $this->pdoStatement->fetchAll();
    }
    public function fetch()
    {
        return $this->pdoStatement->fetch();
    }
}
