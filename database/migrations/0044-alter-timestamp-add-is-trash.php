<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ADD COLUMN is_trash bool NOT NULL DEFAULT 'no'
EOT;
        $db->query($query);

        $query = <<<EOT
        CREATE INDEX timestamp_is_trash
        ON public.timestamp (is_trash);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        DROP COLUMN is_trash;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
