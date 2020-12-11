<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN failed_logins int NOT NULL DEFAULT 0,
        ADD COLUMN suspend_start timestamp with time zone,
        ADD COLUMN locked bool NOT NULL DEFAULT 'no';
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN failed_logins,
        DROP COLUMN suspend_start,
        DROP COLUMN locked;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
