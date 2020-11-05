<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN time_zone text COLLATE pg_catalog."default";
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN time_zone;;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
