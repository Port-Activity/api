<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN next_event_title text COLLATE pg_catalog."default",
            ADD COLUMN next_event_ts timestamp with time zone,
            ADD COLUMN weight integer NOT NULL DEFAULT 1;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN next_event_title,
            DROP COLUMN next_event_ts,
            DROP COLUMN weight;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
