<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN rta_details JSONB;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN rta_details;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
