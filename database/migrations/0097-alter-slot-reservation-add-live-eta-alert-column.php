<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.slot_reservation
            ADD COLUMN jit_eta_discrepancy_time timestamp with time zone,
            ADD COLUMN jit_eta_alert_state text COLLATE pg_catalog."default" NOT NULL DEFAULT 'green';
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.slot_reservation
            DROP COLUMN jit_eta_discrepancy_time,
            DROP COLUMN jit_eta_alert_state;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
