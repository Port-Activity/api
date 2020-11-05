<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.api_key
        ADD COLUMN bound_user_id bigint;
EOT;
        $db->query($query);

        $query = <<<EOT
        UPDATE public.api_key
        SET bound_user_id = 1;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.api_key
        ALTER COLUMN bound_user_id SET NOT NULL;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.api_key
        ADD CONSTRAINT "api_key_bound_user_id_fkey"
                FOREIGN KEY (bound_user_id) REFERENCES public.user(id);
EOT;
        $db->query($query);


        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.api_key
        DROP COLUMN bound_user_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
