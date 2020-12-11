<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class NotificationServiceTest extends TestCase
{
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Type should be one of there: port, ship, port_call_decision
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
     * @expectedExceptionMessage Invalid assigned values for type: port. Valid assigned value is message.
     */
    public function testPortNotificationInvalidValues1(): void
    {
        $service = new NotificationService();
        $service->add("port", "hi", "1234567");
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port. Valid assigned value is message.
     */
    public function testPortNotificationInvalidValues2(): void
    {
        $service = new NotificationService();
        $service->add("port", "hi", null, "portcallmasterid1");
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port. Valid assigned value is message.
     */
    public function testPortNotificationInvalidValues3(): void
    {
        $service = new NotificationService();
        $service->add("port", "hi", null, null, ["decision1", "decision2"]);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port. Valid assigned value is message.
     */
    public function testPortNotificationInvalidValues4(): void
    {
        $service = new NotificationService();
        $service->add("port", "hi", null, null, null, 1);
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: ship. Valid assigned values are message, ship_imo and parent_id.
     */
    // phpcs:enable
    public function testShipNotificationInvalidValues1(): void
    {
        $service = new NotificationService();
        $service->add("ship", "hi");
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: ship. Valid assigned values are message, ship_imo and parent_id.
     */
    // phpcs:enable
    public function testShipNotificationInvalidValues2(): void
    {
        $service = new NotificationService();
        $service->add("ship", "hi", "1234567", "portcallmasterid1");
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: ship. Valid assigned values are message, ship_imo and parent_id.
     */
    // phpcs:enable
    public function testShipNotificationInvalidValues3(): void
    {
        $service = new NotificationService();
        $service->add("ship", "hi", "1234567", null, ["decision1", "decision2"]);
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions.
     */
    // phpcs:enable
    public function testPortCallDecisionNotificationInvalidValues1(): void
    {
        $service = new NotificationService();
        $service->add("port_call_decision", "hi");
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions.
     */
    // phpcs:enable
    public function testPortCallDecisionNotificationInvalidValues2(): void
    {
        $service = new NotificationService();
        $service->add("port_call_decision", "hi", "1234567", "portcallmasterid1");
    }
    // phpcs:disable
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid assigned values for type: port_call_decision. Valid assigned values are message, ship_imo, port_call_master_id and decisions.
     */
    // phpcs:enable
    public function testPortCallDecisionNotificationInvalidValues3(): void
    {
        $service = new NotificationService();
        $service->add("port_call_decision", "hi", "1234567", "portcallmasterid1", ["decision1", "decision2"], 1);
    }
}
