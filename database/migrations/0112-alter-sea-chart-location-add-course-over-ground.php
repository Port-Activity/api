<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.sea_chart_vessel_location
        ADD COLUMN course_over_ground_degrees real;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.sea_chart_vessel_location
        DROP COLUMN course_over_ground_degrees;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
