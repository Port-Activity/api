<?php
namespace SMA\PAA;

class Server
{
    private $data;
    private $queryParameters;
    public function __construct(array $data = [], array $queryParameters = [], array $bodyParameters = [])
    {
        $this->data = $data ? $data : $_SERVER;
        $this->queryParameters = $queryParameters ? $queryParameters : $_GET;
        $this->bodyParameters = $bodyParameters;
    }
    private function get($key): string
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : "";
    }
    public function requestUri(): string
    {
        return $this->get("REQUEST_URI");
    }
    public function requestMethod(): string
    {
        return $this->get("REQUEST_METHOD");
    }
    public function hostname(): string
    {
        return $this->get("SERVER_NAME");
    }
    public function authorization()
    {
        return $this->get('HTTP_AUTHORIZATION');
    }
    public function clientTimeZone()
    {
        return $this->get('HTTP_CLIENTTIMEZONE');
    }
    public function isDev(): bool
    {
        return $this->hostname() === "localhost";
    }
    public function bodyParameters(): array
    {
        if ($this->bodyParameters) {
            return $this->bodyParameters;
        }
        $json = file_get_contents('php://input');
        if ($json) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
    public function getQueryParameters(): array
    {
        return $this->queryParameters;
    }
}
