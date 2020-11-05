<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN live_eta timestamp with time zone,
            ADD COLUMN live_eta_details jsonb;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN live_eta,
            DROP COLUMN live_eta_details;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
