<?php
namespace SMA\PAA\SERVICE;

use PHPUnit\Framework\TestCase;

final class ApiKeyServiceTest extends TestCase
{
    public function testGeneratingApiKey(): void
    {
        $service = new ApiKeyService();
        $this->assertEquals(50, strlen($service->generateApiKey()));
    }
}
