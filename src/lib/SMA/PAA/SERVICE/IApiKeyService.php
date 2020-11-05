<?php
namespace SMA\PAA\SERVICE;

interface IApiKeyService
{
    public function userOrApiKeyName(int $id);
    public function getApiKeyId(): ?int;
}
