<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN master_id text COLLATE pg_catalog."default",
            ADD COLUMN master_start timestamp with time zone,
            ADD COLUMN master_end timestamp with time zone,
            ADD COLUMN master_manual bool NOT NULL DEFAULT 'no';
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN master_id,
            DROP COLUMN master_start,
            DROP COLUMN master_end,
            DROP COLUMN master_manual;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
