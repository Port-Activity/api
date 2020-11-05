<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $query = <<<EOT
            INSERT INTO public.vis_rtz_state (name, created_by, modified_by)
            VALUES ('RTA_SENT', 1, 1);
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            DELETE FROM public.vis_rtz_state
            WHERE name = 'RTA_SENT';
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
