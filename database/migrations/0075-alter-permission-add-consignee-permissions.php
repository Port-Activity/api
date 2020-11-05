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
            "manage_user_consignee" => "Manage consignee users", // Can manage consignee users
            "view_own_queue_nomination" => "View own queue nominations", // Can view own queue nominations
            "manage_own_queue_nomination" => "Manage own queue nominations", // Can manage own queue nominations
            "view_all_queue_nomination" => "View all queue nominations", // Can view all queue nominations
            "manage_all_queue_nomination" => "Manage all queue nominations", // Can manage all queue nominations
            "view_queue_slot_reservation" => "View queue slot reservations", // Can view queue slot reservations
            "manage_queue_slot_reservation" => "Manage queue slot reservations" // Can manage queue slot reservations
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
            WHERE name IN ('manage_user_consignee','view_own_queue_nomination','manage_own_queue_nomination','view_all_queue_nomination','manage_all_queue_nomination','view_queue_slot_reservation','manage_queue_slot_reservation');
EOT;
        // phpcs:enable
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
