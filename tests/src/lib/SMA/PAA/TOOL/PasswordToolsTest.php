<?php
namespace SMA\PAA\TOOL;

use PHPUnit\Framework\TestCase;

final class PasswordToolsTest extends TestCase
{
    protected function setUp()
    {
        $passwordRules = '
        {"user":
            {"password_min_length": "4",
             "password_min_unique_chars": "1"}
        }';
        putenv("PASSWORD_RULES=" . $passwordRules);
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password can't be same as username
     */
    public function testPasswordCheckFailsWhenTryingToUseUsernameAsPassword(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("123456789012", "123456789012", "default"));
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password must be at least 12 characters in length
     */
    public function testAdminPasswordRequiresToBeAtLeastTwelveCharacters(): void
    {
        $tools = new PasswordTools();
        $tools->checkPasswordIsOk("foo@bar", "12345678901", "admin");
    }
    public function testAdminPasswordCheckAcceptsTwelveCharactersLongPassword(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "123456789012", "admin"));
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Weak password. Not enough unique characters in password.
     */
    public function testAdminPasswordContainsAtLeastThreeDifferentChars(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "aaaaaaaabbbb", "admin"));
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password must be at least 4 characters in length
     */
    public function testUserPasswordRequiresToBeAtLeastFourCharacters(): void
    {
        $tools = new PasswordTools();
        $tools->checkPasswordIsOk("foo@bar", "123", "user");
    }
    public function testUserPasswordCheckAcceptsFourCharactersLongPassword(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "1234", "user"));
    }
    public function testUserPasswordCheckAcceptsAllSameChars(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "aaaa", "user"));
    }
}
