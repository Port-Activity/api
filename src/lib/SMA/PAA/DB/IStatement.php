<?php
namespace SMA\PAA\DB;

interface IStatement
{
    public function fetchAll();
    public function fetch();
}
