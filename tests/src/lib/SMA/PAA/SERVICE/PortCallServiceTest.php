<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\ORM\PortCallModel;

final class PortCallServiceTest extends TestCase
{
    public function testDummyPlaceHolderTest(): void
    {
        $service = new PortCallService();
        $this->assertEquals(true, !empty($service));
    }
}
