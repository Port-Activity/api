<?php
namespace SMA\PAA\TOOL;

use PHPUnit\Framework\TestCase;

final class EmailToolsTest extends TestCase
{
    public function testEmailValidatorFailsWithBadEmail(): void
    {
        $tools = new EmailTools();
        $this->assertFalse($tools->isValid("foo@foo"));
    }
    public function testEmailValidatorValidatesCorrectEmail(): void
    {
        $tools = new EmailTools();
        $this->assertEquals("foo@foo.fi", $tools->isValid("foo@foo.fi"));
    }
    public function testSplittingEmailsToArray()
    {
        $tools = new EmailTools();
        $this->assertEquals(["foo@foo.fi", "foo2@xdlol.fi"], $tools->parseAndValidate("foo@foo.fi foo2@xdlol.fi"));
    }
    public function testSplittingEmailsToArrayWhenInvalidEmails()
    {
        $tools = new EmailTools();
        $this->assertFalse($tools->parseAndValidate("foo@foo.fi foo2@xdlol"));
    }
}
