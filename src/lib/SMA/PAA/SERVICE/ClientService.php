<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\TimestampRepository;
use SMA\PAA\ORM\TimestampPrettyModel;

class ClientService
{
    private function buildTimestamp(TimestampPrettyModel $model, $keys)
    {
        return $model->buildValues($keys);
    }

    public function getAllTimestamps(int $limit)
    {
        $repository = new TimestampRepository();
        $results = $repository->getAllTimestampsPretty($limit);

        $res = [];
        foreach ($results as $result) {
            $res[] = $this->buildTimestamp(
                $result,
                [
                    "imo"
                    ,"vessel_name"
                    ,"time_type"
                    ,"state"
                    ,"time"
                    ,"payload"
                    ,"created_by"
                    ,"created_at"
                    ,"modified_by"
                    ,"modified_at"
                ]
            );
        }

        return array_reverse($res);
    }

    private function reduceFormat(array $results)
    {

        if (isset($results[0])) {
            $vesselName = $results[0]->vessel_name;

            $res["vessel_name"] = $vesselName;

            $innerRes = [];

            foreach ($results as $result) {
                $innerRes[] = $this->buildTimestamp(
                    $result,
                    ["time_type","state","time", "created_at", "created_by", "payload", "weight"]
                );
            }

            $res["timestamps"] = array_reverse($innerRes);
        } else {
            $res["vessel_name"] = "";
            $res["timestamps"] = [];
        }

        return $res;
    }
    public function getTimestamps(int $imo)
    {
        $repository = new TimestampRepository();
        return $this->reduceFormat($repository->getTimestampsPretty($imo));
    }
    public function getTimestampsByPortCall(int $portCallId)
    {
        $repository = new TimestampRepository();
        return $this->reduceFormat($repository->getTimestampsForPortCallPretty($portCallId));
    }
}
