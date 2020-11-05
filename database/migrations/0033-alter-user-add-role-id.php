<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        ADD COLUMN role_id serial;
EOT;
        $db->query($query);

        $queryDefaultRoleId = <<<EOT
        UPDATE public.user
        SET role_id = (SELECT id FROM public.role WHERE name = ?);
EOT;
        $db->query(
            $queryDefaultRoleId,
            "inactive_user"
        );

        $queryUpdateRoleId = <<<EOT
        UPDATE public.user
        SET role_id = (SELECT id FROM public.role WHERE name = ?)
        WHERE id IN (SELECT user_id FROM public.backup_role WHERE name = ?);
EOT;

        $roles = [
            "inactive_user"
            ,"user"
            ,"first_user"
            ,"second_admin"
            ,"admin"
        ];

        foreach ($roles as $role) {
            $db->query(
                $queryUpdateRoleId,
                $role,
                $role
            );
        }

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.user
        ALTER COLUMN role_id SET NOT NULL,
        ADD CONSTRAINT "public_role_id_fkey"
                FOREIGN KEY (role_id) REFERENCES public.role ON DELETE CASCADE;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.user
        DROP COLUMN role_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
