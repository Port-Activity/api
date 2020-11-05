<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\CURL\ICurlRequest;
use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\TOOL\AinoTools;

class ResendTools
{
    private $resendRules;
    private $curlRequest;

    public function __construct(ICurlRequest $curlRequest = null)
    {
        if (!isset($curlRequest)) {
            $this->curlRequest = new CurlRequest();
        } else {
            $this->curlRequest = $curlRequest;
        }

        $this->readResendRules();
    }

    private function readResendRules()
    {
        $tokens = explode(";", getenv("API_RESEND_RULES"));
        foreach ($tokens as $token) {
            $rule = explode(",", $token);
            if (sizeof($rule) === 3) {
                $slave = [];
                $slave["slave_url"] = $rule[1];
                $slave["slave_api_key"] = $rule[2];
                $this->resendRules[$rule[0]][] = $slave;
            }
        }
    }

    private function getResendRules(int $userId): array
    {
        if (isset($this->resendRules[$userId])) {
            return $this->resendRules[$userId];
        }

        return [];
    }

    private function sendToSlave(string $url, string $apiKey, array $result)
    {
        $postPayload = json_encode($result);

        $this->curlRequest->init($url);
        $this->curlRequest->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlRequest->setOption(CURLOPT_HEADER, 1);

        $header = [];
        $header[] = "Content-Type: application/json";
        $header[] = "Accept: application/json";
        $header[] = "Authorization: ApiKey " . $apiKey;
        $this->curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
        $this->curlRequest->setOption(CURLOPT_POST, 1);
        $this->curlRequest->setOption(CURLOPT_POSTFIELDS, $postPayload);

        $curlResponse = $this->curlRequest->execute();

        // TODO: Do we have to care about the response, e.g. retry x times?
        $curlFailed = false;
        $info = $this->curlRequest->getInfo();
        if ($info["http_code"] !== 200) {
            $this->curlRequest->close();
            $decoded = json_decode($curlResponse, true);

            if (isset($decoded["error"])) {
                $curlFailed = true;
            }
            if (isset($decoded["result"])) {
                if ($decoded["result"] === "ERROR") {
                    $curlFailed = true;
                }
            }
            $curlFailed = true;
        }

        $ainoTools = new AinoTools();
        $ainoFlowId = $ainoTools->resultChecksum($result);
        $ainoApplication = parse_url($url, PHP_URL_HOST);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $ainoPayload = "timestamp";
        if (strpos($urlPath, "logistics") !== false) {
            $ainoPayload = "logistics-timestamp";
        }

        if ($curlFailed) {
            $ainoTools->sendAinoTransactionTimestampToSlave($ainoApplication, false, $ainoPayload, [], [], $ainoFlowId);
        } else {
            $ainoTools->sendAinoTransactionTimestampToSlave($ainoApplication, true, $ainoPayload, [], [], $ainoFlowId);
        }
    }

    public function resend(int $userId, array $args)
    {
        $resendRules = $this->getResendRules($userId);

        foreach ($resendRules as $resendRule) {
            $this->sendToSlave($resendRule["slave_url"], $resendRule["slave_api_key"], $args);
        }
    }
}
