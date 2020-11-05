<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN slot_reservation_id bigint REFERENCES public.slot_reservation(id) ON DELETE SET NULL,
            ADD COLUMN slot_reservation_status text COLLATE pg_catalog."default",
            ADD COLUMN rta_window_start timestamp with time zone,
            ADD COLUMN rta_window_end timestamp with time zone,
            ADD COLUMN laytime interval;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN slot_reservation_id,
            DROP COLUMN slot_reservation_status,
            DROP COLUMN rta_window_start,
            DROP COLUMN rta_window_end,
            DROP COLUMN laytime;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
