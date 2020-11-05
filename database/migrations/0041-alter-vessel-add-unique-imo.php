<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.vessel
        ADD CONSTRAINT "vessel_unique_imo" UNIQUE (imo)
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.vessel
        DROP CONSTRAINT vessel_unique_imo;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
