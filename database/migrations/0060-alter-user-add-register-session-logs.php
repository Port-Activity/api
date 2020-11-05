<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN last_session_time timestamp with time zone DEFAULT NULL,
        ADD COLUMN registration_code_id bigint REFERENCES public.registration_codes ON DELETE SET NULL DEFAULT NULL;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX last_session_time_idx
        ON public.user (last_session_time, registration_code_id);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN last_session_time,
        DROP COLUMN registration_code_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
