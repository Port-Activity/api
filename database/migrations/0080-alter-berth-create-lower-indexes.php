<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            CREATE INDEX index_lower_code
            ON public.berth
            USING btree
            (lower(code));
            ;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
            CREATE INDEX index_lower_name
            ON public.berth
            USING btree
            (lower(name));
            ;
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP INDEX public.index_lower_code;
EOT;
        $db->query($query);
        $query = <<<EOT
        DROP INDEX public.index_lower_name;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
