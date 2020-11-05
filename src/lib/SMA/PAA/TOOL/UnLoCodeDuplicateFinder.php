<?php
namespace SMA\PAA\TOOL;

use Exception;

# Finds duplicate entries from given UN/LOCODE csv file for given country code
# Usage:
# php UnLoCodeDuplicateFinder.php <Country code> <Path to UN/LOCODE csv file>
# E.g.:
# php src/lib/SMA/PAA/TOOL/UnLoCodeDuplicateFinder.php FI src/lib/SMA/PAA/SERVICE/code-list.csv

if (empty($argv[1])) {
    throw new Exception("Country code not given as argument!");
}

if (empty($argv[2])) {
    throw new Exception("UN/LOCODE csv file not given as argument!");
}

$countryCode = $argv[1];
$csvFile = $argv[2];

$csvLines = file($csvFile);

if ($csvLines === false) {
    throw new Exception("Cannot open given UN/LOCODE csv file: " . $csvFile);
}

$csv = array_map('str_getcsv', $csvLines);
array_walk(
    $csv,
    function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    }
);
array_shift($csv);

$csvCountryLocations = [];
foreach ($csv as $csvEntry) {
    if ($csvEntry["Country"] === $countryCode) {
        $csvCountryLocations[$csvEntry["Location"]][] = implode(",", $csvEntry);
    }
}
ksort($csvCountryLocations);

print("\n");
foreach ($csvCountryLocations as $csvCountryLocation) {
    if (count($csvCountryLocation) > 1) {
        print(implode("\n", $csvCountryLocation) . "\n");
    }
}
