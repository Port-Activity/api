<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.role_permission
        (
            id serial NOT NULL,
            role_id serial NOT NULL REFERENCES public.role(id) ON DELETE CASCADE,
            permission_id serial NOT NULL REFERENCES public.permission(id) ON DELETE CASCADE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT role_permission_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_role_permission UNIQUE (role_id, permission_id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX role_permission_role_id_permission_id_idx
        ON public.role_permission (role_id, permission_id);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.role_permission (role_id, permission_id, created_by, modified_by)
            SELECT r.id, p.id, ?, ?
            FROM public.role r, public.permission p
            WHERE p.name = ?
            AND r.name = ?;

EOT;

        $permissions = [];
        $permissions["admin"] = [
            "login"
            , "basic_user_action"
            , "manage_user_admin"
            , "manage_user_second_admin"
            , "manage_user_first_user"
            , "manage_user_user"
            , "manage_user_inactive_user"
            , "manage_registration_code"
            , "manage_permission"
            , "manage_translation"
            , "manage_setting"
            , "manage_api_key"
            , "send_push_notification"
            , "delete_push_notification"
            , "send_rta_web_form"
            , "add_manual_timestamp"
            , "manage_port_call"
        ];
        $permissions["second_admin"] = [
            "login"
            , "basic_user_action"
            , "manage_user_second_admin"
            , "manage_user_first_user"
            , "manage_user_user"
            , "manage_user_inactive_user"
            , "manage_registration_code"
            , "manage_setting"
            , "manage_api_key"
            , "send_push_notification"
            , "delete_push_notification"
            , "send_rta_web_form"
            , "add_manual_timestamp"
            , "manage_port_call"
        ];
        $permissions["first_user"] = [
            "login"
            , "basic_user_action"
            , "send_push_notification"
            , "send_rta_web_form"
            , "add_manual_timestamp"
        ];
        $permissions["user"] = [
            "login"
            , "basic_user_action"
        ];

        foreach ($permissions as $role => $role_permissions) {
            foreach ($role_permissions as $role_permission) {
                $db->query(
                    $queryDefaultValues,
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
        DROP TABLE public.role_permission;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
