<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testPasswordHash(): void
    {
        $auth = new AuthService();
        $this->assertTrue(
            $auth->hash("foo") !== "foo"
        );
    }
    public function testPasswordHashVerifyVerifiesHashWithCorrectPassword(): void
    {
        $auth = new AuthService();
        $hash = $auth->hash("foo");
        $this->assertTrue(
            $auth->verify("foo", $hash)
        );
    }
    public function testPasswordHashVerifyFailsWithInCorrectPassword(): void
    {
        $auth = new AuthService();
        $hash = $auth->hash("foo");
        $this->assertFalse(
            $auth->verify("bar", $hash)
        );
    }
    public function testGeneratingRandomPassword(): void
    {
        $auth = new AuthService();
        $password = $auth->randomPassword();
        $this->assertEquals(
            30,
            strlen($password)
        );
    }
    public function testGeneratingRandomIsDifferentNextTime(): void
    {
        $auth = new AuthService();
        $password = $auth->randomPassword();
        $password2 = $auth->randomPassword();
        $this->assertEquals(
            30,
            strlen($password)
        );
        $this->assertNotEquals(
            $password,
            $password2
        );
    }
}
