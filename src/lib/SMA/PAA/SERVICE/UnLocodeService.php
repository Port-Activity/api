<?php
namespace SMA\PAA\SERVICE;

class UnLocodeService
{
    public function __construct(IStateService $stateService = null)
    {
        if (!$stateService) {
            $stateService = new StateService();
        }
        $this->stateService = $stateService;
    }
    public function codeToCity(string $unLocode): ?string
    {
        $country = substr($unLocode, 0, 2);
        if (!preg_match("/^[A-Z]{2}$/", $country)) {
            return "";
        }
        $service = $this->stateService;
        return $service->getSet(
            "unlocode." . $unLocode,
            function () use ($unLocode, $country) {
                $filename = "code-list-" . $country . ".csv";
                $fullPath = __DIR__ . "/unlocode/" . $filename;
                if (is_file($fullPath)) {
                    $fileLines = file($fullPath);
                    if ($fileLines === false) {
                        return $unLocode;
                    }
                    foreach ($fileLines as $fileLine) {
                        $csvLine = str_getcsv($fileLine);
                        if (sizeof($csvLine) > 2 && $csvLine[1] . $csvLine[2] === $unLocode) {
                            return preg_replace('/( \(.*?\))/', '', $csvLine[3]);
                        }
                    }
                }
                return $unLocode; // return code itself since not found to cache result
            },
            24 * 60 * 60
        );
    }
    public function codeToCitySafe(string $unLocode): string
    {
        try {
            return $this->codeToCity($unLocode);
        } catch (EInvalidUnLocode $e) {
        }
        return $unLocode;
    }
    public function codeToCoordinates(string $unLocode): ?array
    {
        $country = substr($unLocode, 0, 2);
        if (!preg_match("/^[A-Z]{2}$/", $country)) {
            return null;
        }

        $res = null;

        $filename = "code-list-" . $country . ".csv";
        $fullPath = __DIR__ . "/unlocode/" . $filename;

        if (is_file($fullPath)) {
            $fileLines = file($fullPath);
            if ($fileLines === false) {
                return null;
            }
            foreach ($fileLines as $fileLine) {
                $csvLine = str_getcsv($fileLine);
                if (sizeof($csvLine) > 10 && $csvLine[1] . $csvLine[2] === $unLocode) {
                    $coords = explode(" ", $csvLine[10]);
                    if (sizeof($coords) != 2) {
                        return null;
                    }

                    $lat = $coords[0];
                    $lon = $coords[1];

                    $ns = substr($lat, -1);
                    $ew = substr($lon, -1);

                    if (!(($ns === "N" || $ns === "S") && ($ew === "E" || $ew === "W"))) {
                        return null;
                    }

                    $latDeg = ltrim(substr($lat, 0, 2), "0");
                    $latDeg = empty($latDeg) ? "0" : $latDeg;
                    $latMin = ltrim(substr($lat, 2, 2), "0");
                    $latMin = empty($latMin) ? "0" : $latMin;

                    $lonDeg = ltrim(substr($lon, 0, 3), "0");
                    $lonDeg = empty($lonDeg) ? "0" : $lonDeg;
                    $lonMin = ltrim(substr($lon, 3, 2), "0");
                    $lonMin = empty($lonMin) ? "0" : $lonMin;

                    $latDD = $latDeg + ($latMin / 60);
                    $lonDD = $lonDeg + ($lonMin / 60);

                    $res["lat"] = ($ns === "N") ? $latDD : -$latDD;
                    $res["lon"] = ($ew === "E") ? $lonDD : -$lonDD;
                }
            }
        }

        return $res;
    }
}
