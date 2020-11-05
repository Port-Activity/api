<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN vis_status;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN vis_status text;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
