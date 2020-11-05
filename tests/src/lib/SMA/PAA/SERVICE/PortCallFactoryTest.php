<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class PortCallFactoryTest extends TestCase
{
    private function template()
    {
        $templateService = new PortCallTemplateService();
        return $templateService->build(
            file_get_contents(__DIR__ . "/../../../../../../src/lib/port_call_template_gavle.txt")
        );
    }
    public function dataProvider()
    {
        $testDatas = [];
        $service = new PortCallFactory(new FakeApiKeyService());
        $template = $this->template();
        $dir = __DIR__  . '/port-call-data/';
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (preg_match("/.*data\.json$/", $entry)) {
                    $name = str_replace("-data.json", "", $entry);
                    $data = file_get_contents($dir . $entry);

                    /*
                    file_put_contents(
                        $dir . $name . "-result.json",
                        json_encode(
                            $service->timestampsToPortCall(
                                $template,
                                json_decode($data, true)
                            ),
                        JSON_PRETTY_PRINT)
                    );
                    */

                    $result = file_get_contents($dir . $name . "-result.json");
                    $testDatas[$name] = [
                        json_decode($data, true),
                        json_decode($result, true)
                    ];
                }
            }
        }
        return $testDatas;
    }
    /**
     * @dataProvider dataProvider
     */
    public function testPortCallFormatting($timestamps, $expected): void
    {
        $template = $this->template();
        $service = new PortCallFactory(new FakeApiKeyService());
        $this->assertEquals(
            print_r($expected, true),
            print_r($service->timestampsToPortCall($template, $timestamps), true),
        );
    }
}
