<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.api_key
        ADD COLUMN name text COLLATE pg_catalog."default";
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.api_key
        DROP COLUMN name;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
