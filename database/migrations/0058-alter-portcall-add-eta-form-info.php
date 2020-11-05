<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ADD COLUMN berth text COLLATE pg_catalog."default",
            ADD COLUMN eta_form_email text COLLATE pg_catalog."default";
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            DROP COLUMN berth,
            DROP COLUMN eta_form_email;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
