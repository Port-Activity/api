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
            "manage_user_consignee"
            , "view_own_queue_nomination"
            , "manage_own_queue_nomination"
            , "view_all_queue_nomination"
            , "manage_all_queue_nomination"
            , "view_queue_slot_reservation"
            , "manage_queue_slot_reservation"
        ];
        $permissions["second_admin"] = [
            "manage_user_consignee"
            , "view_own_queue_nomination"
            , "manage_own_queue_nomination"
            , "view_all_queue_nomination"
            , "manage_all_queue_nomination"
            , "view_queue_slot_reservation"
            , "manage_queue_slot_reservation"
        ];
        $permissions["consignee"] = [
            "login"
            , "view_own_queue_nomination"
            , "manage_own_queue_nomination"
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
        // phpcs:disable
        $query = <<<EOT
            DELETE FROM public.role_permission
            WHERE permission_id IN
            (SELECT p.id FROM public.permission p
            WHERE p.name IN ('manage_user_consignee','view_own_queue_nomination','manage_own_queue_nomination','view_all_queue_nomination','manage_all_queue_nomination','view_queue_slot_reservation','manage_queue_slot_reservation'));
EOT;
        // phpcs:enable
        $db->query($query);

        unset($query);
        $query = <<<EOT
            DELETE FROM public.role_permission
            WHERE permission_id =
            (SELECT p.id FROM public.permission p
            WHERE p.name = 'login')
            AND role_id =
            (SELECT r.id FROM public.role r
            WHERE r.name = 'consignee');
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
