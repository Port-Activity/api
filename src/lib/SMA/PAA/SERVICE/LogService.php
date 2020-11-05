<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\Session;
use SMA\PAA\Server;
use SMA\PAA\Response;

class LogService
{
    private $transport;
    private $dateService;
    public function __construct(ITransport $transport = null, IDateService $dateService = null)
    {
        $this->transport = $transport ? $transport : new TransportRedis();
        $this->dateService = $dateService ? $dateService : new DateService();
    }
    private function removeNewLines($string)
    {
        return str_replace(array("\n", "\r"), "", $string);
    }
    private function encodeDelimiters(array $datas)
    {
        return array_map(
            function ($string) {
                return str_replace("|", "<pipe>", $string);
            },
            $datas
        );
    }
    private function removeSecretFields(array $data)
    {
        if (array_key_exists("password", $data)) {
            $data["password"] = "<removed>";
        }
        return $data;
    }
    public function log(Session $session, Server $server, Response $response, $executionTime)
    {
        $limit = getenv("LOG_LIMIT");
        $ts = $this->dateService->now();
        $encoded = $this->encodeDelimiters([
            $ts,
            strval($executionTime),
            $session->userId(),
            $server->requestMethod(),
            $server->requestUri(),
            strval($response->code()),
            json_encode($this->removeSecretFields($server->bodyParameters()))
        ]);
        $this->transport->push(
            $this->removeNewLines(
                join("|", $encoded)
            ),
            $limit
        );
        return true;
    }
}
