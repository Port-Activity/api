<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $queryDefaultValues = <<<EOT
            INSERT INTO public.role (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $roles = [
            "consignee" => "Consignee"
        ];

        foreach ($roles as $key => $value) {
            $db->query(
                $queryDefaultValues,
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
            DELETE FROM public.role
            WHERE name = 'consignee';
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
