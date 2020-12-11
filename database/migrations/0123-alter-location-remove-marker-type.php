<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.sea_chart_vessel_location DROP COLUMN sea_chart_marker_type_id;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.sea_chart_vessel_location
        ADD COLUMN sea_chart_marker_type_id integer DEFAULT 1 NOT NULL REFERENCES public.sea_chart_marker_type(id);
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
