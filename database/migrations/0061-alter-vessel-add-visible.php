<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vessel
            ADD COLUMN visible bool NOT NULL DEFAULT 'yes'
EOT;
        $db->query($query);

        $query = <<<EOT
        CREATE INDEX vessel_visible
        ON public.vessel (visible);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vessel
            DROP COLUMN visible;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
