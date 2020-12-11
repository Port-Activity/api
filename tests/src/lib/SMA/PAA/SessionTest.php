<?php
namespace SMA\PAA;

use PHPUnit\Framework\TestCase;
use SMA\PAA\ORM\FakeConnection;
use SMA\PAA\ORM\OrmRepository;

final class SessionTest extends TestCase
{
    public function testSessionGetsUserIdFromSession(): void
    {
        $session = new Session(["user_id" => 1]);
        $this->assertEquals(1, $session->userId());
    }
    public function testSessionGetsUserForSession(): void
    {
        OrmRepository::injectFakeDb(new FakeConnection());
        $session = new Session(["user_id" => 111]);
        $this->assertEquals(
            '{"email":null,"first_name":null,"last_name":null,"role_id":null,'
            . '"registration_code_id":null,"status":"active","locked":"f","registration_type":"","id":111,'
            . '"created_at":null,"created_by":null,"modified_at":null,"modified_by":null}',
            json_encode($session->user())
        );
    }
}
