<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $queryDefaultValues = <<<EOT
            INSERT INTO public.setting (name, value, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $db->query(
            $queryDefaultValues,
            "queue_module",
            "enabled",
            1,
            1
        );


        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DELETE FROM public.setting WHERE name='queue_module';
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
