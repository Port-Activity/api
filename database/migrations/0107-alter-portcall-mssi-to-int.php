<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ALTER COLUMN mmsi TYPE integer USING (mmsi::numeric::integer);
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            ALTER TABLE public.port_call
            ALTER COLUMN mmsi TYPE text;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
