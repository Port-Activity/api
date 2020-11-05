<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\Session;

final class UserServiceTest extends TestCase
{
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password must be at least 12 characters in length
     */
    public function testRegisterFailsWhenPasswordIsTooShort(): void
    {
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
