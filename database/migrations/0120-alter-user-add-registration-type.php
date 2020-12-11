<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN registration_type text COLLATE pg_catalog."default";
EOT;
        $db->query($query);

        $query = <<<EOT
        UPDATE public.user
        SET registration_type='manual' WHERE registration_code_id IS NULL;
EOT;
        $db->query($query);

        $query = <<<EOT
        UPDATE public.user
        SET registration_type='code' WHERE registration_code_id IS NOT NULL;
EOT;
        $db->query($query);

        $query = <<<EOT
        ALTER TABLE public.user ALTER COLUMN registration_type SET NOT NULL;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN registration_type;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
