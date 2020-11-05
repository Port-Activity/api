<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        unset($query);
        $query = <<<EOT
        DELETE FROM public.push_notification_token;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.push_notification_token
        DROP CONSTRAINT pushnotificationtoken_user_id_installation_id_platform_key,
        ADD UNIQUE (installation_id, platform);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DELETE FROM public.push_notification_token;
EOT;
        $db->query($query);

        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.push_notification_token
        DROP CONSTRAINT pushnotificationtoken_installation_id_platform_key,
        ADD UNIQUE (user_id, installation_id, platform);
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
