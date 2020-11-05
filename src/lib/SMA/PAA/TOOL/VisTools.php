<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\RemoteServerException;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\CURL\ICurlRequest;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\ORM\VisMessageRepository;
use SMA\PAA\ORM\VisMessagePrettyModel;
use SMA\PAA\ORM\VisNotificationRepository;
use SMA\PAA\ORM\VisNotificationPrettyModel;
use SMA\PAA\ORM\VisVoyagePlanRepository;
use SMA\PAA\ORM\VisVoyagePlanPrettyModel;

class VisTools
{
    private $curlRequest;

    public function __construct(ICurlRequest $curlRequest)
    {
        $this->curlRequest = $curlRequest;
    }

    public function callVisAgent(string $call, string $method, array $parameters = [], string $json = ""): string
    {
        $url = getenv("VIS_AGENT_API_URL");
        $url .= $call;

        foreach ($parameters as $key => $value) {
            $url .= "/" . $key . ":" . $value;
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
            $this->curlRequest->setOption(CURLOPT_POSTFIELDS, $json);
        }

        $response = $this->curlRequest->execute();
        $info = $this->curlRequest->getInfo();
        $decoded = json_decode($response, true);
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

        return $body;
    }

    public function visVesselDataFromServiceId(string $serviceId)
    {
        $visVesselRepository = new VisVesselRepository();
        $visVesselModel = $visVesselRepository->getWithServiceId($serviceId);

        if (!isset($visVesselModel)) {
            $this->callVisAgent("find-services", "GET", ["service-id" => $serviceId]);
        }
    }

    public function getTextMessage(
        string $fromServiceId = null,
        string $toServiceId = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $query = [];
        $query["message_type"] = "TXT";
        if (isset($fromServiceId)) {
            $query["from_service_id"] = $fromServiceId;
        }
        if (isset($toServiceId)) {
            $query["to_service_id"] = $toServiceId;
        }

        $offset = isset($offset) ? $offset : 0;
        $limit = isset($limit) ? $limit : 0;
        $sort = isset($sort) ? $sort : "time";

        $visMessageRepository = new VisMessageRepository();
        $rawResults = $visMessageRepository->listPaginated($query, $offset, $limit, $sort);

        $prettyData = [];
        foreach ($rawResults["data"] as $rawResult) {
            $prettyResult = new VisMessagePrettyModel();
            $prettyResult->setFromVisMessage($rawResult);
            $prettyData[] = $prettyResult;
        }

        $res["data"] = $prettyData;
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }

    public function getNotification(
        string $fromServiceId = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $query = [];
        if (isset($toServiceId)) {
            $query["to_service_id"] = $toServiceId;
        }

        $offset = isset($offset) ? $offset : 0;
        $limit = isset($limit) ? $limit : 0;
        $sort = isset($sort) ? $sort : "time";

        $visNotificationRepository = new VisNotificationRepository();
        $rawResults = $visNotificationRepository->listPaginated($query, $offset, $limit, $sort);

        $prettyData = [];
        foreach ($rawResults["data"] as $rawResult) {
            $prettyResult = new VisNotificationPrettyModel();
            $prettyResult->setFromVisNotification($rawResult);
            $prettyData[] = $prettyResult;
        }

        $res["data"] = $prettyData;
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }

    public function getService(
        int $imo = null,
        string $serviceId = null
    ) {
        $params = [];
        if (isset($imo)) {
            $params["imo"] = $imo;
        }
        if (isset($serviceId)) {
            $params["service-id"] = $serviceId;
        }

        $this->callVisAgent("find-services", "GET", $params);
    }

    public function getServicesByCoordinates(array $coords): ?array
    {
        if (!isset($coords["lat"]) || !isset($coords["lon"])) {
            return null;
        }

        $res = json_decode($this->callVisAgent("find-services-by-coordinates", "POST", [], json_encode($coords)), true);

        if (empty($res)) {
            return null;
        }

        return $res;
    }

    public function getInterPortServicesByLocode(string $locode): ?array
    {
        $params["locode"] = $locode;

        $res = json_decode($this->callVisAgent("find-inter-port-services-by-locode", "GET", $params), true);

        if (empty($res)) {
            return null;
        }

        return $res;
    }

    public function getVessel(
        int $imo = null,
        string $vesselName = null,
        string $serviceId = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $query = [];
        if (isset($imo)) {
            $query["imo"] = $imo;
        }
        if (isset($vesselName)) {
            $query["vessel_name"] = $vesselName;
        }
        if (isset($serviceId)) {
            $query["service_id"] = $serviceId;
        }

        $offset = isset($offset) ? $offset : 0;
        $limit = isset($limit) ? $limit : 0;
        $sort = isset($sort) ? $sort : "id";

        $visVesselRepository = new VisVesselRepository();
        $res = $visVesselRepository->listPaginated($query, $offset, $limit, $sort);

        return $res;
    }

    public function getVoyagePlan(
        string $fromServiceId = null,
        string $toServiceId = null,
        int $offset = null,
        int $limit = null,
        string $sort = null
    ): array {
        $res = [];

        $query = [];
        $query["message_type"] = "RTZ";
        if (isset($fromServiceId)) {
            $query["from_service_id"] = $fromServiceId;
        }
        if (isset($toServiceId)) {
            $query["to_service_id"] = $toServiceId;
        }

        $offset = isset($offset) ? $offset : 0;
        $limit = isset($limit) ? $limit : 0;
        $sort = isset($sort) ? $sort : "time";

        $visVoyagePlanRepository = new VisVoyagePlanRepository();
        $rawResults = $visVoyagePlanRepository->listPaginated($query, $offset, $limit, $sort);

        $prettyData = [];
        foreach ($rawResults["data"] as $rawResult) {
            $prettyResult = new VisVoyagePlanPrettyModel();
            $prettyResult->setFromVisVoyagePlan($rawResult);
            $prettyData[] = $prettyResult;
        }

        $res["data"] = $prettyData;
        $res["pagination"] = $rawResults["pagination"];

        return $res;
    }

    public function uploadTextMessage(
        string $visServiceId,
        string $author,
        string $subject,
        string $body,
        string $informationObjectReferenceId = null,
        string $informationObjectReferenceType = null,
        string $area = null
    ) {
        $visVesselRepository = new VisVesselRepository();
        $visVesselModel = $visVesselRepository->getWithServiceId($visServiceId);

        if (!isset($visVesselModel)) {
            throw new InvalidParameterException("VIS service ID not found: " . $visServiceId);
        }

        if (!isset($visVesselModel->service_url)) {
            throw new InvalidParameterException("VIS service URL not found: " . $visServiceId);
        }

        $postPayload = [];
        $postPayload["to_service_id"] = $visServiceId;
        $postPayload["to_url"] = $visVesselModel->service_url;
        $postPayload["author"] = $author;
        $postPayload["subject"] = $subject;
        $postPayload["body"] = $body;
        if (isset($informationObjectReferenceId)) {
            $postPayload["information_object_reference_id"] = $informationObjectReferenceId;
        }
        if (isset($informationObjectReferenceType)) {
            $postPayload["information_object_reference_type"] = $informationObjectReferenceType;
        }
        if (isset($area)) {
            $postPayload["area"] = $area;
        }

        $this->callVisAgent("upload-text-message", "POST", [], json_encode($postPayload));
    }

    public function sendRta(
        string $visServiceId,
        string $rta,
        string $etaMin,
        string $etaMax
    ) {
        $visVesselRepository = new VisVesselRepository();
        $visVesselModel = $visVesselRepository->getWithServiceId($visServiceId);

        if (!isset($visVesselModel)) {
            throw new InvalidParameterException("VIS service ID not found: " . $visServiceId);
        }

        if (!isset($visVesselModel->service_url)) {
            throw new InvalidParameterException("VIS service URL not found: " . $visServiceId);
        }

        $visVoyagePlanRepository = new VisVoyagePlanRepository();
        $visVoyagePlanModel = $visVoyagePlanRepository->first(["from_service_id" => $visServiceId], "time DESC");

        if (!isset($visVoyagePlanModel)) {
            throw new InvalidParameterException("Cannot find voyage plan for VIS service ID: " . $visServiceId);
        }

        $payloadArray = json_decode($visVoyagePlanModel->payload, true);

        if (!isset($payloadArray["stmMessage"])) {
            throw new InvalidParameterException(
                "Cannot find stmMessage from voyage plan ID: " . $visVoyagePlanModel->id
            );
        }
        if (!isset($payloadArray["stmMessage"]["message"])) {
            throw new InvalidParameterException(
                "Cannot find stmMessage->message from voyage plan ID: " . $visVoyagePlanModel->id
            );
        }
        $rtz = $payloadArray["stmMessage"]["message"];

        $postPayload = [];
        $postPayload["to_service_id"] = $visServiceId;
        $postPayload["to_url"] = $visVesselModel->service_url;
        $postPayload["rtz_parse_results"] = $visVoyagePlanModel->rtz_parse_results;
        $postPayload["rtz"] = $rtz;
        $postPayload["rta"] = $rta;
        $postPayload["eta_min"] = $etaMin;
        $postPayload["eta_max"] = $etaMax;

        $this->callVisAgent("send-rta", "POST", [], json_encode($postPayload));
    }

    public function sendDeparture(
        string $toVisServiceId,
        string $toVisUrl,
        string $fromLocode,
        string $toLocode,
        int $vesselImo,
        string $vesselName,
        float $toLat,
        float $toLon,
        string $time
    ) {
        $postPayload = [];
        $postPayload["to_service_id"] = $toVisServiceId;
        $postPayload["to_url"] = $toVisUrl;
        $postPayload["from_locode"] = $fromLocode;
        $postPayload["to_locode"] = $toLocode;
        $postPayload["vessel_imo"] = $vesselImo;
        $postPayload["vessel_name"] = $vesselName;
        $postPayload["to_lat"] = $toLat;
        $postPayload["to_lon"] = $toLon;
        $postPayload["time"] = $time;

        $this->callVisAgent("send-departure", "POST", [], json_encode($postPayload));
    }

    public function pollVisService()
    {
        $this->callVisAgent("poll-save", "GET");
    }

    public function getVisServiceConfiguration(): string
    {
        $res = "";

        $res = $this->callVisAgent("config", "GET");

        return $res;
    }
}
