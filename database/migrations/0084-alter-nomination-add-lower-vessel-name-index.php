<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            CREATE INDEX index_nomination_lower_vessel_name
            ON public.nomination
            USING btree
            (lower(vessel_name));
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP INDEX public.index_nomination_lower_vessel_name;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
