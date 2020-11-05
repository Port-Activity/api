<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ADD COLUMN weight int NOT NULL DEFAULT 0;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        DROP COLUMN weight;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
