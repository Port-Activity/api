<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\InvalidParameterException;

final class VesselServiceTest extends TestCase
{
    public function testVesselTypes(): void
    {
        $service = new VesselServiceExt();

        $expectedResult = array();
        $expectedResult[] = ["id" => 1, "name" => "Random Type Name"];
        $service->fakeVesselTypeRepository->listReturnValue = $expectedResult;

        $vesselTypes = $service->vesselTypes();

        $this->assertEquals($expectedResult, $vesselTypes);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Vessel does not exist
     */
    public function testUpdateVesselInvalidId(): void
    {
        $service = new VesselServiceExt();
        $service->updateVessel(null, 1);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Vessel does not exist
     */
    public function testUpdateVesselWithNonExistingId(): void
    {
        $service = new VesselServiceExt();
        $service->updateVessel(1, 1);
    }
    /**
     * @expectedException SMA\PAA\InvalidParameterException
     * @expectedExceptionMessage Invalid vessel type
     */
    public function testUpdateVesselInvalidType(): void
    {
        $service = new VesselServiceExt();
        $service->fakeVesselRepository->getReturnValue = true;
        $service->updateVessel(1, null);
    }
    public function testUpdateVesselSuccess(): void
    {
        $service = new VesselServiceExt();
        $service->fakeVesselRepository->getReturnValue = true;
        $service->fakeVesselRepository->saveReturnValue = 1;
        $result = $service->updateVessel(1, 1);
        $this->assertEquals("OK", $result["result"]);
        $this->assertEquals(2, count($service->fakeStateService->deletedKeys));
        $this->assertEquals(
            StateService::LATEST_PORT_CALLS,
            $service->fakeStateService->deletedKeys[0]
        );
        $this->assertEquals(
            StateService::LATEST_SEA_CHART_VESSELS_AND_MARKERS,
            $service->fakeStateService->deletedKeys[1]
        );
    }
    public function testUpdateVesselFailure(): void
    {
        $service = new VesselServiceExt();
        $service->fakeVesselRepository->getReturnValue = true;
        $service->fakeVesselRepository->saveReturnValue = 0;
        $result = $service->updateVessel(1, 1);
        $this->assertEquals("ERROR", $result["result"]);
        $this->assertEquals("Invalid vessel properties", $result["message"]);
        $this->assertEquals(0, count($service->fakeStateService->deletedKeys));
    }
    public function testUpdateVesselFailureWithThrow(): void
    {
        $service = new VesselServiceExt();
        $service->fakeVesselRepository->getReturnValue = true;
        $service->fakeVesselRepository->saveReturnValue = 0;
        $service->fakeVesselRepository->saveThrows = true;
        $result = $service->updateVessel(1, 1);
        $this->assertEquals("ERROR", $result["result"]);
        $this->assertEquals("Invalid vessel properties", $result["message"]);
        $this->assertEquals(0, count($service->fakeStateService->deletedKeys));
    }
}
