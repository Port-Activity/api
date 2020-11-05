<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $query = <<<EOT
        DELETE FROM notification WHERE REGEXP_REPLACE(ship_imo, '[^0-9]', '', 'g') <> ship_imo;
EOT;
        $db->query($query);

        $query = <<<EOT
        UPDATE notification SET ship_imo='0' WHERE ship_imo = '';
EOT;
        $db->query($query);

        $query = <<<EOT
        ALTER TABLE public.notification
        ALTER COLUMN ship_imo TYPE integer USING (ship_imo::integer);
EOT;
        $db->query($query);

        $query = <<<EOT
        ALTER TABLE public.notification
        ALTER COLUMN ship_imo DROP NOT NULL;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        UPDATE notification SET ship_imo=0 WHERE ship_imo IS NULL;
EOT;
        $db->query($query);
        $query = <<<EOT
        ALTER TABLE public.notification
        ALTER COLUMN ship_imo TYPE text;
EOT;
        $db->query($query);

        $query = <<<EOT
        ALTER TABLE public.notification
        ALTER COLUMN ship_imo SET NOT NULL;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
