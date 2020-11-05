<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vis_voyage_plan
            ADD COLUMN ack BOOLEAN NOT NULL DEFAULT FALSE,
            ADD COLUMN operational_ack BOOLEAN NOT NULL DEFAULT FALSE;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.vis_voyage_plan
            DROP COLUMN ack,
            DROP COLUMN operational_ack;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
