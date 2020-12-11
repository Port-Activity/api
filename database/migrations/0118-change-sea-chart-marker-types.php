<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $db->query("UPDATE public.sea_chart_vessel_location SET sea_chart_marker_type_id=1");
        $db->query("UPDATE public.sea_chart_fixed_vessel SET sea_chart_marker_type_id=1");
        $db->query("DELETE FROM public.sea_chart_marker_type WHERE id > 1");
        $db->query("UPDATE public.sea_chart_marker_type SET name='vessel_gray' WHERE id = 1");
        $db->query("ALTER SEQUENCE public.sea_chart_marker_type_id_seq RESTART WITH 2;");
        
        $queryDefaultMarkerTypes = <<<EOT
            INSERT INTO public.sea_chart_marker_type (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;

        $markerTypes = [
            "vessel_blue"
            ,"vessel_cyan"
            ,"vessel_purple"
            ,"vessel_green"
            // ,"vessel_gray"
            ,"vessel_black"
            ,"vessel_white"
            ,"vessel_red"
            ,"vessel_orange"
        ];

        foreach ($markerTypes as $type) {
            $db->query(
                $queryDefaultMarkerTypes,
                $type,
                1,
                1
            );
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $db->query("UPDATE public.sea_chart_fixed_vessel SET sea_chart_marker_type_id=1");
        return true;
    }
);

$migrate->migrateOrRevert();
