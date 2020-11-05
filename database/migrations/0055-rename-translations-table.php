<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.translations
            RENAME TO translation;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.translation
        RENAME TO translations;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
