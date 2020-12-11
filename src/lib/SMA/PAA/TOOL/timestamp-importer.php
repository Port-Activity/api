<?php
namespace SMA\PAA\TOOL;

require_once __DIR__ . "/../CURL/ICurlRequest.php";
require_once __DIR__ . "/../CURL/CurlRequest.php";
require_once __DIR__ . "/../TOOL/DateTools.php";

use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\TOOL\DateTools;

use Exception;

# Imports timestamps one by one to given API instance from export json
# Input file must be in format created with timestamp export api call
# Api key map file must be in json format:
#    {
#        "api_key_map": [
#            {
#                "description": "MSW",
#                "user_id": "<input file created_by>",
#                "api_key": "<target API key>"
#            },
#            {
#                "description": "Fenix",
#                "user_id": "<input file created_by>",
#                "api_key": "<target API key>"
#            }
#        ]
#    }
# Offset must be in ISO duration format
# Usage:
// phpcs:ignore
# php timestamp-importer.php <Path to export json file> <Path to API key map file> <Offset in ISO duration> <API timestamp URL>
# E.g.:
// phpcs:ignore
# php src/lib/SMA/PAA/TOOL/timestamp-importer.php export.json production_to_staging_api_key_map.json PT1M http://localhost:8000/agent/rest/timestamps

$dateTools = new DateTools();

if (empty($argv[1])) {
    throw new Exception("Input file not given as argument!");
}
$inputFile = $argv[1];
$inputJson = file_get_contents($inputFile);
if ($inputJson === false) {
    throw new Exception("Cannot open input file: " . $inputFile);
}
$inputArray = json_decode($inputJson, true);
if (!isset($inputArray["data"])) {
    throw new Exception("Cannot find data entry from input file: " . $inputFile);
}
$timestamps = $inputArray["data"];

if (empty($argv[2])) {
    throw new Exception("API key map file not given as argument!");
}
$apiKeyMapFile = $argv[2];
$apiKeyMapJson = file_get_contents($apiKeyMapFile);
if ($apiKeyMapJson === false) {
    throw new Exception("Cannot open API key map file: " . $apiKeyMapFile);
}
$apiKeyMapArray = json_decode($apiKeyMapJson, true);
if (!isset($apiKeyMapArray["api_key_map"])) {
    throw new Exception("Cannot find api_key_map entry from API key map file: " . $apiKeyMapFile);
}
$apiKeyMap = [];
foreach ($apiKeyMapArray["api_key_map"] as $apiKeyMapEntry) {
    $apiKeyMap[$apiKeyMapEntry["user_id"]] = $apiKeyMapEntry["api_key"];
}

if (empty($argv[3])) {
    throw new Exception("Offset not given as argument!");
}
$offset = $argv[3];
if (!$dateTools->isValidIsoDuration($offset)) {
    throw new Exception("Given offset is not in ISO duration format: " . $offset);
}

if (empty($argv[4])) {
    throw new Exception("API timestamp URL not given as argument!");
}
$apiTimestampUrl = $argv[4];


$imports = [];
foreach ($timestamps as $timestamp) {
    $timestamp["time"] = $dateTools->addIsoDuration($timestamp["time"], $offset);
    $imports[$timestamp["id"]] = $timestamp;
}
ksort($imports);

foreach ($imports as $import) {
    print(
        $import["time"] . " " .
        $import["time_type"] . " " .
        $import["state"] . "\n"
    );
}

$curlRequest = new CurlRequest();

foreach ($imports as $import) {
    print("\n----------------------------------------\n");
    print("Send:\n");

    print(
        $import["time"] . " " .
        $import["time_type"] . " " .
        $import["state"] . "\n"
    );

    print("Y/n/exit? ");

    $input = trim(fgets(STDIN));
    if ($input === "exit") {
        exit(0);
    } elseif ($input === "n") {
        // Do nothing
    } else {
        if (!array_key_exists($import["created_by"], $apiKeyMap)) {
            throw new Exception("API key mapping not found for created_by: " . $import["created_by"]);
        }
        $apiKey = $apiKeyMap[$import["created_by"]];
        $resultArray["imo"] = $import["imo"];
        $resultArray["vessel_name"] = $import["vessel_name"];
        $resultArray["time_type"] = $import["time_type"];
        $resultArray["state"] = $import["state"];
        $resultArray["time"] = str_replace(" ", "T", $import["time"]);
        $resultArray["payload"] = json_decode($import["payload"], true);
        $curlRequest->init($apiTimestampUrl);
        $curlRequest->setOption(CURLOPT_POSTFIELDS, json_encode($resultArray));
        $header = array();
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: ApiKey " . $apiKey;
        $curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
        $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlResponse = $curlRequest->execute();
        print_r($curlResponse);
        $info = $curlRequest->getInfo();
        if ($info["http_code"] !== 200) {
            $curlRequest->close();
            $decoded = json_decode($curlResponse, true);

            if (isset($decoded["error"])) {
                print_r("Error response from server ".$apiTimestampUrl.":\n".print_r($decoded, true)."\n");
            }
            if (isset($decoded["result"])) {
                if ($decoded["result"] === "ERROR") {
                    print_r("Error result from server ".$apiTimestampUrl.":\n".print_r($decoded, true)."\n");
                }
            }
            print_r("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
        }
    }
}
