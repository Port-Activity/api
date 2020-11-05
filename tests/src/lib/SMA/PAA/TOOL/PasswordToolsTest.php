<?php
namespace SMA\PAA\TOOL;

use PHPUnit\Framework\TestCase;

final class PasswordToolsTest extends TestCase
{
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password must be at least 12 characters in length
     */
    public function testPasswordRequiresToBeAtLeastTwelveCharacters(): void
    {
        $tools = new PasswordTools();
        $tools->checkPasswordIsOk("foo@bar", "12345678901");
    }
    public function testPasswordCheckAcceptsTwelveCharactersLongPassword(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "123456789012"));
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Password can't be same as username
     */
    public function testPasswordCheckFailsWhenTryingToUseUsernameAsPassword(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("123456789012", "123456789012"));
    }
    /**
     * @expectedException SMA\PAA\AuthenticationException
     * @expectedExceptionMessage Weak password. Not enough unique characters in password.
     */
    public function testPasswordContainsAtLeastThreeDifferentChars(): void
    {
        $tools = new PasswordTools();
        $this->assertTrue($tools->checkPasswordIsOk("foo@bar", "aaaaaaaabbbb"));
    }
}
