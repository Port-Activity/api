<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\RemoteServerException;
use SMA\PAA\CURL\ICurlRequest;

class TimestampClusterTools
{
    private $curlRequest;

    public function __construct(ICurlRequest $curlRequest)
    {
        $this->curlRequest = $curlRequest;
    }

    public function callTimestampClusterAgent(string $call, string $method, array $parameters = []): array
    {
        $url = getenv("TIMESTAMP_CLUSTER_AGENT_API_URL");
        $url .= "/" . $call;

        foreach ($parameters as $parameter) {
            $url .= "/" . $parameter;
        }

        $this->curlRequest->init($url);
        $this->curlRequest->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlRequest->setOption(CURLOPT_HEADER, 1);

        $header = [];
        $header[] = "Content-Type: application/json";
        $header[] = "Accept: application/json";
        $this->curlRequest->setOption(CURLOPT_HTTPHEADER, $header);

        if ($method === "POST") {
            $this->curlRequest->setOption(CURLOPT_POST, 1);
            $this->curlRequest->setOption(CURLOPT_POSTFIELDS, $parameters);
        }

        $response = $this->curlRequest->execute();
        $info = $this->curlRequest->getInfo();
        $headerSize = $this->curlRequest->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $this->curlRequest->close();

        if ($info["http_code"] !== 200) {
            throw new RemoteServerException(
                "Error occured during curl exec.\ncurl_getinfo returns:\n" . print_r($info, true) . "\n"
                . "Response body:\n". print_r($body, true) . "\n"
            );
        }

        $res = json_decode($body, true);

        if (isset($res["result"])) {
            if ($res["result"] === "ERROR") {
                return [];
            }
        }

        return $res;
    }

    public function getTimestampClustersForImo(int $imo): array
    {
        $res = $this->callTimestampClusterAgent("cluster-timestamps", "GET", [$imo]);

        return $res;
    }
}
