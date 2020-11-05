<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN berth_name text COLLATE pg_catalog."default";
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN berth_name;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
