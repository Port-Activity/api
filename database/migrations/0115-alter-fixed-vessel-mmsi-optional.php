<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.sea_chart_fixed_vessel ALTER mmsi DROP NOT NULL;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.sea_chart_fixed_vessel ALTER mmsi SET NOT NULL;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
