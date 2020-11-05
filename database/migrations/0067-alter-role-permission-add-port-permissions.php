<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
            INSERT INTO public.role_permission (role_id, permission_id, created_by, modified_by)
            SELECT r.id, p.id, ?, ?
            FROM public.role r, public.permission p
            WHERE p.name = ?
            AND r.name = ?;
EOT;

        $permissions = [];
        $permissions["admin"] = [
            "manage_port"
        ];

        foreach ($permissions as $role => $role_permissions) {
            foreach ($role_permissions as $role_permission) {
                $db->query(
                    $query,
                    1,
                    1,
                    $role_permission,
                    $role
                );
            }
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            DELETE FROM public.role_permission
            WHERE permission_id IN
            (SELECT p.id FROM public.permission p
            WHERE p.name IN ('manage_port'));
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
