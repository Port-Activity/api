<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            INSERT INTO public.permission (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;

        $permissions = [
            "manage_vessel" => "Manage vessel properties"
        ];

        foreach ($permissions as $key => $value) {
            $db->query(
                $query,
                $key,
                $value,
                1,
                1
            );
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            DELETE FROM public.permission
            WHERE name IN ('manage_vessel');
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
