<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\Session;
use SMA\PAA\ORM\FakeConnection;
use SMA\PAA\ORM\OrmRepository;

final class UserServiceTest extends TestCase
{
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password must be at least 12 characters in length
     */
    public function testRegisterFailsWhenPasswordIsTooShort(): void
    {
        OrmRepository::injectFakeDb(
            new FakeConnection(
                ["enabled" => "t",
                    "code" => "thecode",
                    "role" => "user",
                    "description" => "User code"]
            )
        );
        $service = new UserService();
        $service->register("jack", "theripper", "thecode", "foo@bar", "pwtooshort");
    }
    /**
     * @expectedException SMA\PAA\InvalidOperationException
     * @expectedExceptionMessage You can't delete yourself
     */
    public function testDeletingSelfFails(): void
    {
        $service = new UserService(new Session(["user_id" => 123]));
        $service->delete(123);
    }
}
