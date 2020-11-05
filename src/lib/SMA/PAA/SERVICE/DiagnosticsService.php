<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\DB\Connection;

class DiagnosticsService
{
    public function healtCheck()
    {
        return "OK";
    }
    public function hello()
    {
        return "Hello";
    }
    public function echo(string $string)
    {
        return "echoing $string";
    }
    public function echoTwo(string $string, string $string2)
    {
        return "echoing $string and $string2";
    }
    public function echoArray(array $values)
    {
        $out = "";
        foreach ($values as $k => $v) {
            $out .= "$k=$v\n";
        }
        return $out;
    }
    public function phpinfo()
    {
        phpinfo();
    }
    public function dbtest()
    {
        $db = Connection::get();
        return $db->queryOne('SELECT 1 as moro, 2 as moro2, now();');
    }
}
