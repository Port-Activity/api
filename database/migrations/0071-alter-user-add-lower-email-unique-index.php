<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            CREATE UNIQUE INDEX index_lower_email
            ON public.user
            USING btree
            (lower(email));
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP INDEX public.index_lower_email;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
