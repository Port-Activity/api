<?php
namespace SMA\PAA\TOOL;

require_once __DIR__ . "/../CURL/ICurlRequest.php";
require_once __DIR__ . "/../CURL/CurlRequest.php";

use SMA\PAA\CURL\CurlRequest;

use Exception;

# Imports timestamps one by one to given API instance from export json
# Input file must be in format created with timestamp export api call
# Usage:
# php TimestampImporter.php <Path to export json file> <API key> <API timestamp URL>
# E.g.:
// phpcs:ignore
# php src/lib/SMA/PAA/TOOL/TimestampImporter.php export.json 8d7b31936350f87530c9ce0e21743a1cedd31d83567062f34c http://localhost:8000/agent/rest/timestamps

if (empty($argv[1])) {
    throw new Exception("Input file not given as argument!");
}

if (empty($argv[2])) {
    throw new Exception("API key not given as argument!");
}

if (empty($argv[3])) {
    throw new Exception("API timestamp URL not given as argument!");
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

$imports = [];
foreach ($timestamps as $timestamp) {
    $imports[$timestamp["id"]] = $timestamp;
}
ksort($imports);

foreach ($imports as $import) {
    print(
        $import["created_at"] . " " .
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
        $import["state"] . " " .
        $import["created_at"] . "\n"
    );

    print("Y/n/exit? ");

    $input = trim(fgets(STDIN));
    if ($input === "exit") {
        exit(0);
    } elseif ($input === "n") {
        // Do nothing
    } else {
        $resultArray["imo"] = $import["imo"];
        $resultArray["vessel_name"] = $import["vessel_name"];
        $resultArray["time_type"] = $import["time_type"];
        $resultArray["state"] = $import["state"];
        $resultArray["time"] = str_replace(" ", "T", $import["time"] . "00");
        $resultArray["payload"] = json_decode($import["payload"], true);
        $curlRequest->init($argv[3]);
        $curlRequest->setOption(CURLOPT_POSTFIELDS, json_encode($resultArray));
        $header = array();
        $header[] = "Content-type: application/json";
        $header[] = "Authorization: ApiKey " . $argv[2];
        $curlRequest->setOption(CURLOPT_HTTPHEADER, $header);
        $curlRequest->setOption(CURLOPT_RETURNTRANSFER, true);
        $curlResponse = $curlRequest->execute();
        print_r($curlResponse);
        $info = $curlRequest->getInfo();
        if ($info["http_code"] !== 200) {
            $curlRequest->close();
            $decoded = json_decode($curlResponse, true);

            if (isset($decoded["error"])) {
                throw new Exception("Error response from server ".$argv[3].":\n".print_r($decoded, true)."\n");
            }
            if (isset($decoded["result"])) {
                if ($decoded["result"] === "ERROR") {
                    throw new Exception("Error result from server ".$argv[3].":\n".print_r($decoded, true)."\n");
                }
            }
            throw new Exception("Error occured during curl exec.\ncurl_getinfo returns:\n".print_r($info, true)."\n");
        }
    }
}
