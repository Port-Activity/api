<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.notification
        ADD COLUMN parent_notification_id bigint REFERENCES public.notification(id) ON DELETE CASCADE;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.notification
        DROP COLUMN parent_notification_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
