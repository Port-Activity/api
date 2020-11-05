<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN last_login_time timestamp with time zone,
        ADD COLUMN last_login_data jsonb;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX last_login_time_idx
        ON public.user (last_login_time);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN last_login_time,
        DROP COLUMN last_login_data;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
