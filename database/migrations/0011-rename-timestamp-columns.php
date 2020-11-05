<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        RENAME COLUMN vesselname TO vessel_name;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.timestamp
        RENAME COLUMN ts TO time;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        RENAME COLUMN vessel_name TO vesselname;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.timestamp
        RENAME COLUMN time TO ts;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
