<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.migration
            ALTER COLUMN created_at SET DEFAULT current_timestamp;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.migration
            ALTER COLUMN created_at SET DEFAULT CURRENT_DATE;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
