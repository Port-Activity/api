<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\InvalidParameterException;
use SMA\PAA\TOOL\DateTools;
use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\SERVICE\PortCallService;

class ExportService
{
    public function timestamps(
        int $id = null,
        int $imo = null,
        int $port_call_id = null,
        string $start_date_time = null,
        string $end_date_time = null,
        int $offset = null,
        int $limit = 100,
        string $sort = null
    ): array {
        $res = [];

        $dateTools = new DateTools();
        if (isset($start_date_time)) {
            if (!$dateTools->isValidIsoDateTime($start_date_time)) {
                throw new InvalidParameterException(
                    "Given start date time is not in ISO format: " . $start_date_time
                );
            }
        }

        if (isset($end_date_time)) {
            if (!$dateTools->isValidIsoDateTime($end_date_time)) {
                throw new InvalidParameterException(
                    "Given end date time is not in ISO format: " . $end_date_time
                );
            }
        }

        if (isset($limit)) {
            if ($limit < 0) {
                throw new InvalidParameterException(
                    "Limit cannot be negative. Given limit: " . $limit
                );
            }

            if ($limit > 100) {
                throw new InvalidParameterException(
                    "Maximum limit is 100 records. Given limit: " . $limit
                );
            }
        }

        $repository = new TimestampRepository();
        $res = $repository->exportTimestamps(
            $id,
            $imo,
            $port_call_id,
            $start_date_time,
            $end_date_time,
            $offset,
            $limit,
            $sort
        );

        return $res;
    }

    public function portcalls(): array
    {
        $res = [];

        $portCallService = new PortCallService();
        $res = $portCallService->portCalls();

        unset($res["pinned_vessels"]);

        return $res;
    }
}
