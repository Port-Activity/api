<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.user
            ADD COLUMN status text COLLATE pg_catalog."default" NOT NULL DEFAULT 'active';
EOT;
        $db->query($query);
        $query = <<<EOT
            CREATE INDEX user_status_index ON public.user (status);
EOT;
        $db->query($query);
        $query = <<<EOT
            UPDATE public.user SET status='api_user' WHERE first_name = 'ApiKey' and last_name = 'ApiKey';
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.user
            DROP COLUMN status;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
