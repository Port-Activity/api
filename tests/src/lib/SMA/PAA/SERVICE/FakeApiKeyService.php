<?php
namespace SMA\PAA\SERVICE;

class FakeApiKeyService implements IApiKeyService
{
    public function userOrApiKeyName(int $id)
    {
        return "Test api key";
    }

    public function getApiKeyId(): ?int
    {
        return 1;
    }
}
