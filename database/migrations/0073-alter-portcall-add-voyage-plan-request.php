<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN voyage_plan_request timestamp with time zone;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN voyage_plan_request;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
