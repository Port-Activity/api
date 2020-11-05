<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\CURL\CurlRequest;
use SMA\PAA\TOOL\VisTools;
use SMA\PAA\ORM\PortRepository;
use SMA\PAA\ORM\PortModel;
use SMA\PAA\ORM\VisVesselRepository;
use SMA\PAA\SERVICE\UnLocodeService;

class OutboundVesselTools
{
    private function resolveVisDepartureData(string $toLocode): ?array
    {
        $unLocodeService = new UnLocodeService();
        $coords = $unLocodeService->codeToCoordinates($toLocode);

        if ($coords === null) {
            return null;
        }

        $res["to_lat"] = $coords["lat"];
        $res["to_lon"] = $coords["lon"];
        $res["services"] = [];

        $portRepository = new PortRepository();
        $portModels = $portRepository->getByLocode($toLocode);

        if ($portModels !== null) {
            foreach ($portModels as $portModel) {
                $visVesselRepository = new VisVesselRepository();
                $visVesselModel = $visVesselRepository->getWithServiceId($portModel->service_id);

                if (!isset($visVesselModel)) {
                    $visTools = new VisTools(new CurlRequest());
                    $visTools->getService(null, $portModel->service_id);
                    $visVesselModel = $visVesselRepository->getWithServiceId($portModel->service_id);
                }

                if ($portModel->getIsWhiteListOut() && isset($visVesselModel)) {
                    $innerRes["to_service_id"] = $visVesselModel->service_id;
                    $innerRes["to_url"] = $visVesselModel->service_url;

                    $res["services"][] = $innerRes;
                }
            }

            return $res;
        } else {
            $visTools = new VisTools(new CurlRequest());
            $visServices = $visTools->getInterPortServicesByLocode($toLocode);

            if ($visServices === null) {
                return null;
            }

            foreach ($visServices as $visService) {
                $portModel = new PortModel();
                $portModel->set($visService["name"], $visService["service_id"], true, true, [$toLocode]);
                $portRepository->save($portModel);
            }

            # Recursion to get data for the new port model
            return $this->resolveVisDepartureData($toLocode);
        }
    }

    public function sendEtdToNextPort(int $imo, string $vessel_name, string $toLocode, string $time)
    {
        $unixTimestamp = strtotime($time);
        if ($unixTimestamp === false) {
            error_log("Cannot convert time time to unix timestamp: " . $time);
            return;
        }

        $visTime = date("Y-m-d\TH:i:sP", $unixTimestamp);
        if ($visTime === false) {
            error_log("Cannot convert time time to VIS format: " . $time);
            return;
        }

        $visDepartureData = $this->resolveVisDepartureData($toLocode);

        if ($visDepartureData === null) {
            error_log("Cannot resolve VIS departure data for locode: " . $toLocode);
            return;
        }

        if (empty($visDepartureData["services"])) {
            error_log("No services found for locode: " . $toLocode);
            return;
        }

        $services = $visDepartureData["services"];

        # Do not send fake imo
        $imoStr = strval($imo);
        $imoLength = strlen($imoStr);
        if ($imoLength !== 7) {
            $imo = 0;
        }

        $visTools = new VisTools(new CurlRequest());

        # Send ETD to each service mapped to next port locode
        foreach ($services as $service) {
            $visTools->sendDeparture(
                $service["to_service_id"],
                $service["to_url"],
                getenv("VIS_PORT_UNLOCODE"),
                $toLocode,
                $imo,
                $vessel_name,
                $visDepartureData["to_lat"],
                $visDepartureData["to_lon"],
                $visTime
            );
        }
    }
}
