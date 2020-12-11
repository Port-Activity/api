<?php
namespace SMA\PAA;

use PHPUnit\Framework\TestCase;
use SMA\PAA\SERVICE\FakeStateService;

final class AppTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $app = new App(new Server([]), new FakeStateService());
        $this->assertInstanceOf(
            "SMA\PAA\App",
            $app
        );
    }
    public function testRouteAliasFindsWithPath(): void
    {
        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/" => "public:Diagnostics:hello"
        ]);
        $this->assertEquals(
            "Hello",
            $app->run()
        );
    }
    public function testRouteAliasFindsWithPathAndParameters(): void
    {
        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/echo/foo"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/echo/:string" => "public:Diagnostics:echo"
        ]);
        $this->assertEquals(
            "echoing foo",
            $app->run()
        );
    }
    public function testRouteAliasFindsWithPathAndMultipleParameters(): void
    {
        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/echo/foo/bar"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/echo/:string/:string2" => "public:Diagnostics:echoTwo"
        ]);
        $this->assertEquals(
            "echoing foo and bar",
            $app->run()
        );
    }
    public function testRouteAliasWithDifferentProtocolsWithSamePathName(): void
    {
        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/echo/fooget"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/echo" => "public:Diagnostics:echo"
            ,"GET:/echo/:string" => "public:Diagnostics:echo"
        ]);
        $this->assertEquals(
            "echoing fooget",
            $app->run()
        );

        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/echo"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/echo" => "public:Diagnostics:hello"
            ,"GET:/echo/:string" => "public:Diagnostics:echo"
        ]);
        $this->assertEquals(
            "Hello",
            $app->run()
        );
    }

    public function testRouteAliasWithDifferentProtocolsWithSamePathNameWhenQueryStrings(): void
    {
        $app = new App(
            new Server(["REQUEST_METHOD" => "GET", "REQUEST_URI" => "/echo?limit=10"]),
            new FakeStateService()
        );
        $app->setAliases([
            "GET:/echo" => "public:Diagnostics:hello"
            ,"GET:/echo/:string" => "public:Diagnostics:echo"
        ]);
        $this->assertEquals(
            "Hello",
            $app->run()
        );
    }
    public function testAllParametersAreTrimmed(): void
    {
        $app = new App(
            new Server(
                ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/echo"],
                [],
                ["string" => "  trim me "]
            ),
            new FakeStateService()
        );
        $app->setAliases([
            "POST:/echo" => "public:Diagnostics:echo"
        ]);
        $this->assertEquals(
            "echoing trim me",
            $app->run()
        );
    }
    public function testParametersTrimmedDoesntFailOnArrays(): void
    {
        $app = new App(
            new Server(
                ["REQUEST_METHOD" => "POST", "REQUEST_URI" => "/echo"],
                [],
                ["values" => ["foo" => " bar "]]
            ),
            new FakeStateService()
        );
        $app->setAliases([
            "POST:/echo" => "public:Diagnostics:echoArray"
        ]);
        $this->assertEquals(
            "foo= bar \n",
            $app->run()
        );
    }
}
