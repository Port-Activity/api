<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class UnLocodeServiceTest extends TestCase
{
    public function testTransformingUnLocodeToHumanReadableCityName(): void
    {
        $service = new UnLocodeService(new FakeStateService());
        $this->assertEquals('Rauma', $service->codeToCity("FIRAU"));
        $this->assertEquals('Gävle', $service->codeToCity("SEGVX"));
    }
    public function testTransformingUnknownUnLocodeToCityNameReturnsLocodeItself(): void
    {
        $service = new UnLocodeService(new FakeStateService());
        $this->assertEquals('FXRAU', $service->codeToCity("FXRAU"));
    }
    public function testCodeToCoordinates(): void
    {
        $service = new UnLocodeService(new FakeStateService());
        $this->assertEquals(["lat" => "61.133333333333", "lon" => "21.5"], $service->codeToCoordinates("FIRAU"));
        $this->assertEquals(["lat" => "-33.85", "lon" => "151.2"], $service->codeToCoordinates("AUSYD"));
        $this->assertEquals(
            ["lat" => "-34.583333333333336", "lon" => "-58.666666666666664"],
            $service->codeToCoordinates("ARBUE")
        );
        $this->assertEquals(["lat" => "40.7", "lon" => "-74"], $service->codeToCoordinates("USNYC"));
        $this->assertEquals(
            ["lat" => "49.416666666666664", "lon" => "0.23333333333333334"],
            $service->codeToCoordinates("FRHON")
        );
    }
}
