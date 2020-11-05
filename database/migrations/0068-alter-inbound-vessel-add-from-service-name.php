<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.inbound_vessel
            ADD COLUMN from_service_name text COLLATE pg_catalog."default" NOT NULL DEFAULT ''
EOT;
        $db->query($query);

        $query = <<<EOT
        CREATE INDEX inbound_vessel_from_service_name
        ON public.inbound_vessel (from_service_name);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.inbound_vessel
            DROP COLUMN from_service_name;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
