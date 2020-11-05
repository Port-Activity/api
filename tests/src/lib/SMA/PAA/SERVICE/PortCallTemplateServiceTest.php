<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class PortCallTemplateServiceTest extends TestCase
{
    public function testBuildingPortCalls(): void
    {
        $service = new PortCallTemplateService();
        $this->assertEquals([[
            "group" => "atSea",
            "time_type" => "Foo",
            "state" => "Bar",
            "payload" => ["direction" => "inbound"],
        ]], $service->build("atSea:Foo:Bar:direction=inbound"));
    }
}
