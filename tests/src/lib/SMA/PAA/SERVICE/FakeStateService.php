<?php
namespace SMA\PAA\SERVICE;

class FakeStateService implements IStateService
{
    public function get(string $key)
    {
    }
    public function getSet(string $key, callable $callback)
    {
        return call_user_func($callback);
    }
    public function delete(string $key)
    {
    }
}
