<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ADD COLUMN port_call_id bigint,
        ADD CONSTRAINT "timestamp_port_call_id_fkey"
                FOREIGN KEY (port_call_id) REFERENCES public.port_call(id);
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        DROP COLUMN port_call_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
