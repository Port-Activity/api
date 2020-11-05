<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.registration_codes
        ADD COLUMN description text COLLATE pg_catalog."default" NOT NULL DEFAULT '';
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.registration_codes
        DROP COLUMN description;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
