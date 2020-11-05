<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;
use SMA\PAA\Session;
use SMA\PAA\Response;

final class LogServiceTest extends TestCase
{
    public function testLogStringIsGeneratedWithDelimiters(): void
    {
        $transport = new FakeTransport();
        $service = new LogService($transport, new FakeDateService());
        $service->log(
            new Session(["user_id" => 123]),
            new FakeServer([]),
            new Response(),
            0.1234
        );
        $this->assertEquals('2019-10-24T23:10:18Z|0.1234|123||||{"a":1,"b":"c<pipe>d"}', $transport->message);
    }
    public function testLogStringIsEncodesDelimiters(): void
    {
        $transport = new FakeTransport();
        $service = new LogService($transport, new FakeDateService());
        $service->log(
            new Session(["user_id" => 123]),
            new FakeServer(["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/foo/bar?baz"]),
            new Response(200),
            0.1234
        );
        $this->assertEquals(
            '2019-10-24T23:10:18Z|0.1234|123|POST|/foo/bar?baz|200|{"a":1,"b":"c<pipe>d"}',
            $transport->message
        );
    }
    public function testLogStringIsStrippingNewLines(): void
    {
        $transport = new FakeTransport();
        $service = new LogService($transport, new FakeDateService());
        $service->log(
            new Session(["user_id" => "123\n"]),
            new FakeServer([]),
            new Response(200),
            0.1234
        );
        $this->assertEquals(
            '2019-10-24T23:10:18Z|0.1234|123|||200|{"a":1,"b":"c<pipe>d"}',
            $transport->message
        );
    }
}
