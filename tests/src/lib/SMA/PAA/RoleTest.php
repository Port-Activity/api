<?php
namespace SMA\PAA;

use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testRoleEqualityIsEqualForSameRoles(): void
    {
        $this->assertTrue(Role::PUBLIC()->Equals(Role::PUBLIC()));
    }
    public function testRoleEqualityIsNotEqualForDifferentRoles(): void
    {
        $this->assertFalse(Role::PUBLIC()->Equals(Role::ADMIN()));
    }
}
