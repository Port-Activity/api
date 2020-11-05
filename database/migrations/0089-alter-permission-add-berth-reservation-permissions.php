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
            "view_berth" => "View berths", // Can view berths
            "manage_berth_reservation" => "Manage berth reservations", // Can manage berth reservations
            "view_berth_reservation" => "View berth reservations", // Can view berth reservations
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
            WHERE name IN ('view_berth','manage_berth_reservation','view_berth_reservation');
EOT;
        // phpcs:enable
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
