<?php
namespace SMA\PAA\DB;

use PHPUnit\Framework\TestCase;

final class ConnectTest extends TestCase
{
    /**
     * @expectedException Exception
     */
    public function testDbConnectionFailsIfTryingToInitiateDirectlyConstructor(): void
    {
        $db = new Connection("foo", "localhost", 5432, "paa", "username", "password");
    }
}
