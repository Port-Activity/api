<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Type should be one of there: port, ship
     */
    public function testUnknownNotificationTypeFails(): void
    {
        $service = new NotificationService();
        $service->add("footype", "hi");
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Ship imo must be number
     */
    public function testAddingNonNumericShipImoFails(): void
    {
        $service = new NotificationService();
        $service->add("ship", "hi", "1a");
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Ship imo can't be assigned when message type is 'port'
     */
    public function testShipImoMustBeNullWhenAddingPortMessageAdding(): void
    {
        $service = new NotificationService();
        $service->add("port", "hi", "1234567");
    }
}
