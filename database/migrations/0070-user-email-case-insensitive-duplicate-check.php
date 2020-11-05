<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            SELECT count(*), lower(email)
            FROM public.user
            GROUP BY lower(email)
            HAVING count(*) > 1;
EOT;
        $res = $db->queryAll($query);
        if (!empty($res)) {
            throw new \Exception("Duplicate emails in public.user. Cannot proceed." . print_r($res, true));
        }
        return true;
    },
    function () {
        return true;
    }
);

$migrate->migrateOrRevert();
