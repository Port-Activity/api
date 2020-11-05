<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.slot_reservation
            ADD COLUMN port_call_id bigint REFERENCES public.port_call(id) ON DELETE SET NULL;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.slot_reservation
            DROP COLUMN port_call_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
