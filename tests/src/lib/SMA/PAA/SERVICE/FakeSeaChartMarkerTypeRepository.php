<?php
namespace SMA\PAA\SERVICE;

class FakeSeaChartMarkerTypeRepository
{
    public function markerNameById(int $typeId): string
    {
        if ($typeId === 1) {
            return "markerTypeUnknown";
        } elseif ($typeId === 20) {
            return "markerTypeFixed";
        } else {
            return "markerType" . $typeId;
        }
    }
    public function list(array $query, int $start, int $count): ?array
    {
        $result = [];
        $result[] =  array(
            "name" => "timeline",
            "id" => 1,
            "created_at" => "2020-11-03 00:00:00+00",
            "created_by" => 1,
            "modified_at" => "2020-11-03 00:00:00+00",
            "modified_by" => 1
        );
        return $result;
    }
}
