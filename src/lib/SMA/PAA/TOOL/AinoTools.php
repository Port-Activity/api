<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\AINO\AinoClient;

class AinoTools
{
    private $userIdAinoApiKeyMap;
    private $application;
    private $ainoApiKey;

    public function __construct()
    {
        $baseUrl = getenv("BASE_URL");
        if (empty($baseUrl)) {
            $this->application = "UNKNOWN";
        } else {
            $this->application = parse_url($baseUrl, PHP_URL_HOST);
        }

        $this->ainoApiKey = getenv("AINO_APIKEY");
        if (!$this->ainoApiKey) {
            $this->ainoApiKey = "";
        }
    }

    private function readUserIdAinoApiKeyMap()
    {
        $tokens = explode(";", getenv("USER_ID_AINO_API_KEY_MAP"));
        foreach ($tokens as $token) {
            $rule = explode(",", $token);
            if (sizeof($rule) === 3) {
                $map = [];
                $map["user_id_aino_application"] = $rule[1];
                $map["user_id_aino_api_key"] = $rule[2];
                $this->userIdAinoApiKeyMap[$rule[0]][] = $map;
            }
        }
    }

    private function getUserIdAinoApiKeyMap(int $userId): array
    {
        if (isset($this->userIdAinoApiKeyMap[$userId])) {
            return $this->userIdAinoApiKeyMap[$userId];
        }

        return [];
    }

    private function sendAinoTransaction(
        bool $success,
        string $apiKey,
        string $from,
        string $to,
        string $timestamp,
        string $operation,
        string $payloadType,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        $aino = new AinoClient($apiKey, $from, $to);
        $ainoFunction = $success ? "succeeded" : "failure";
        $message = $from . ($success ? " succeeded" : " failed");

        $aino->$ainoFunction(
            $timestamp,
            $message,
            $operation,
            $payloadType,
            $ids,
            $meta,
            $flowId
        );
    }

    public function resultChecksum(array $result) : ?string
    {
        ksort($result);

        $jsonResult = json_encode($result);

        if ($jsonResult === false) {
            return null;
        }

        return md5($jsonResult);
    }

    public function sendAinoTransactionTimestampFromRemoteService(
        int $userId,
        bool $success,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        $this->readUserIdAinoApiKeyMap();
        $map = $this->getUserIdAinoApiKeyMap($userId);

        foreach ($map as $entry) {
            $this->sendAinoTransaction(
                $success,
                $entry["user_id_aino_api_key"],
                $entry["user_id_aino_application"],
                $this->application,
                gmdate("Y-m-d\TH:i:s\Z"),
                "Post",
                "Timestamp",
                $ids,
                $meta,
                $flowId
            );
        }
    }

    public function sendAinoTransactionTimestampToSlave(
        string $slaveApplication,
        bool $success,
        string $payload,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        if ($this->ainoApiKey !== "") {
            $this->sendAinoTransaction(
                $success,
                $this->ainoApiKey,
                $this->application,
                $slaveApplication,
                gmdate("Y-m-d\TH:i:s\Z"),
                "Post",
                $payload,
                $ids,
                $meta,
                $flowId
            );
        }
    }

    public function sendAinoTransactionSaveTimestamp(
        bool $success,
        string $payload,
        array $ids,
        array $meta,
        string $flowId = null
    ) {
        if ($this->ainoApiKey !== "") {
            $this->sendAinoTransaction(
                $success,
                $this->ainoApiKey,
                $this->application,
                $this->application . " DB",
                gmdate("Y-m-d\TH:i:s\Z"),
                "Save",
                $payload,
                $ids,
                $meta,
                $flowId
            );
        }
    }
}
