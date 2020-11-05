<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\LogisticsTimestampRepository;
use SMA\PAA\ORM\LogisticsTimestampPrettyModel;

class LogisticsService
{

    private function buildLogisticsTimestamp(LogisticsTimestampPrettyModel $model)
    {

        $keys = [
            "time"
            ,"checkpoint"
            ,"direction"
            ,"created_by"
            ,"created_at"
            ,"modified_by"
            ,"modified_at"
        ];
        $decodedKeys = [
            "external_id"
            ,"front_license_plates"
            ,"rear_license_plates"
            ,"containers"
        ];
        $data = $model->buildValues($keys);
        // Note: these values can't picked with buildValues since "non-compatible with db"
        // and here we give values only for ui
        foreach ($decodedKeys as $k) {
            $data[$k] = $model->$k;
        }
        return $data;
    }

    public function list(int $limit)
    {
        $repository = new LogisticsTimestampRepository();
        $results = $repository->getAllLogisticsTimestampsPretty($limit);

        $res = [];
        foreach ($results as $result) {
            $res[] = $this->buildLogisticsTimestamp($result);
        }

        return $res;
    }

    public function one(string $license_plate)
    {
        $repository = new LogisticsTimestampRepository();
        $results = $repository->getLogisticsTimestampsPretty($license_plate);

        $res["license_plate"] = $license_plate;

        if (isset($results[0])) {
            $innerRes = [];

            foreach ($results as $result) {
                $innerRes[] = $this->buildLogisticsTimestamp($result);
            }

            $res["timestamps"] = $innerRes;
        } else {
            $res["timestamps"] = [];
        }

        return $res;
    }

    public function filtered(int $limit)
    {
        $repository = new LogisticsTimestampRepository();
        $filter = getenv("LICENSE_PLATES_FILTER");
        $checkpointsFilter = getenv("CHECKPOINTS_FILTER");
        if ($filter) {
            $licensePlates = explode(
                ",",
                $filter ?: ""
            );
            $licensePlates = array_map(function ($plate) {
                return str_replace("-", "", $plate);
            }, $licensePlates);
            $checkpoints = explode(
                ",",
                $checkpointsFilter ?: ""
            );
            $results = $repository->getLogisticsTimestampsFilteredPretty($limit, $licensePlates, $checkpoints);
        } else {
            $results = $repository->getAllLogisticsTimestampsPretty($limit);
        }

        $res = [];
        foreach ($results as $result) {
            $res[] = $this->buildLogisticsTimestamp($result);
        }

        return $res;
    }
}
