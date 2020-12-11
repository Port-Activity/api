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
            "manage_own_user" => "Manage own users", // Can manage only users created by self
            "manage_all_user" => "Manage all users" // Can manage users created by anybody
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
        // phpcs:disable
        $query = <<<EOT
            DELETE FROM public.permission
            WHERE name IN ('manage_own_user','manage_all_user');
EOT;
        // phpcs:enable
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
