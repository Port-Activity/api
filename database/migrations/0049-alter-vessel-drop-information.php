<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.vessel
        DROP COLUMN nationality,
        DROP COLUMN from_port,
        DROP COLUMN to_port;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.vessel
        ADD COLUMN nationality text COLLATE pg_catalog."default",
        ADD COLUMN from_port text COLLATE pg_catalog."default",
        ADD COLUMN to_port text COLLATE pg_catalog."default";
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
