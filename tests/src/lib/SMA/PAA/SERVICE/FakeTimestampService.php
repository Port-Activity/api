<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\TimestampModel;

class FakeTimestampService implements ITimestampService
{
    public function portCallTimestamps($imo): array
    {
        $out = [];
        $data = json_decode(\file_get_contents(__DIR__ . "/imo-" . $imo . "-timestamps.json"), true);
        foreach ($data["timestamps"] as $timestamp) {
            $model = new TimestampModel();
            foreach ($timestamp as $k => $v) {
                $model->$k = $v;
            }
            $out[] = $model;
        }
        return $out;
    }
}
