<?php
namespace SMA\PAA\TOOL;

require_once __DIR__ . "/../CURL/ICurlRequest.php";
require_once __DIR__ . "/../CURL/CurlRequest.php";

use SMA\PAA\CURL\CurlRequest;

use Exception;

# Rebuilds all port calls one by one for given API instance
# Session ID must be for user that has manage_port_call permission
# Usage:
# php RebuildAllPortCalls.php <Session ID> <API get vessels url> <API rebuild port calls url>
# E.g.:
// phpcs:ignore
# php src/lib/SMA/PAA/TOOL/RebuildAllPortCalls.php 3t2sm62nk4im67uiskpqdb8otb http://localhost:8000/vessels http://localhost:8000/rebuild-port-calls

if (empty($argv[1])) {
    throw new Exception("Session ID not given as argument!");
}

if (empty($argv[2])) {
    throw new Exception("API get vessels URL not given as argument!");
}

if (empty($argv[3])) {
    throw new Exception("API rebuild port calls URL not given as argument!");
}

$sessionId = $argv[1];
$getVesselsUrl = $argv[2];
$rebuildPortCallsUrl = $argv[3];

$curlRequest = new CurlRequest();

print("\n----------------------------------------\n");
print("Fetching vessels\n");

$curlRequest->init($getVesselsUrl);
$header = array();
$header[] = "Content-type: application/json";
$header[] = "Authorization: Bearer " . $sessionId;
$curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
$curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
$curlResponse = $curlRequest->execute();
$info = $curlRequest->getInfo();
$vessels = json_decode($curlResponse, true);
$curlRequest->close();

if ($info["http_code"] !== 200) {
    throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
}

if (isset($decoded["error"])) {
    throw new Exception("Error response from server ".$getVesselsUrl.":\n".print_r($decoded, true)."\n");
}

if (isset($decoded["result"])) {
    if ($decoded["result"] === "ERROR") {
        throw new Exception("Error result from server ".$getVesselsUrl.":\n".print_r($decoded, true)."\n");
    }
}

$vesselCount = count($vessels);

print("Found " . $vesselCount . " vessels\n");

$ct = 1;
foreach ($vessels as $vessel) {
    $imo = $vessel["imo"];
    print("\n----------------------------------------\n");
    print($ct . "/" . $vesselCount . "\n");
    print("Rebuilding port calls for IMO: " . $imo . "\n");

    $curlRequest->init($rebuildPortCallsUrl . "?imo=" . $imo);
    $header = array();
    $header[] = "Content-type: application/json";
    $header[] = "Authorization: Bearer " . $sessionId;
    $curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
    $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
    $curlResponse = $curlRequest->execute();
    $info = $curlRequest->getInfo();
    $decoded = json_decode($curlResponse, true);
    $curlRequest->close();

    print("Curl response: " . $curlResponse . "\n");

    if ($info["http_code"] !== 200) {
        throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
    }

    if (isset($decoded["error"])) {
        throw new Exception("Error response from server ".$rebuildPortCallsUrl.":\n".print_r($decoded, true)."\n");
    }

    if (isset($decoded["result"])) {
        if ($decoded["result"] === "ERROR") {
            throw new Exception("Error result from server ".$rebuildPortCallsUrl.":\n".print_r($decoded, true)."\n");
        }
    }

    print("Done\n");
    $ct += 1;
}
exit(0);
