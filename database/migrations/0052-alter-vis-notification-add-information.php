<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vis_notification
            ADD COLUMN message_id text COLLATE pg_catalog."default",
            ADD COLUMN message_type text COLLATE pg_catalog."default",
            ADD COLUMN notification_type text COLLATE pg_catalog."default",
            ADD COLUMN subject text COLLATE pg_catalog."default";
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_notification_message_id_idx
        ON public.vis_notification (message_id);
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_notification_message_type_idx
        ON public.vis_notification (message_type);
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_notification_notification_type_idx
        ON public.vis_notification (notification_type);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vis_notification
            DROP COLUMN message_id,
            DROP COLUMN message_type,
            DROP COLUMN notification_type,
            DROP COLUMN subject;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
