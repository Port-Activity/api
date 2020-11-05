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
            "view_vis_information" => "View VIS information" // Can view VIS information
            , "send_vis_text_message" => "Send VIS text messages" // Can send VIS text messages
            , "send_vis_rta" => "Send RTA via VIS" // Can send RTA via VIS voyage pland
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
            WHERE name IN ('view_vis_information','send_vis_text_message','send_vis_rta');
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
