<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.permission
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            readable_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT permission_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX permission_name_idx
        ON public.permission (name);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.permission (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $permissions = [
            "login" => "Login" // Can login
            , "basic_user_action" => "Basic user actions" // Can perform basic user actions
            , "manage_user_admin" => "Manage admin users" // Can manage admin users
            , "manage_user_second_admin" => "Manage second admin users" // Can manage second admin users
            , "manage_user_first_user" => "Manage first users" // Can manage first users
            , "manage_user_user" => "Manage normal users"// Can manage normal users
            , "manage_user_inactive_user" => "Manage inactive users" // Can manage inactive users
            , "manage_registration_code" => "Manage registration codes" // Can manage registration codes
            , "manage_permission" => "Manage permissions" // Can manage permissions
            , "manage_translation" => "Manage translations"  // Can manage translations
            , "manage_setting" => "Manage settings"  // Can manage settings
            , "manage_api_key" => "Manage API keys"  // Can manage API keys
            , "send_push_notification" => "Send push notifications"  // Can send push notifications
            , "delete_push_notification" => "Delete push notifications" // Can delete push notifications
            , "send_rta_web_form" => "Send RTA via web form" // Can send RTA via web form
            , "add_manual_timestamp" => "Add timestamps manually" // Can add timestamps manually
            , "manage_port_call" => "Manage port calls" // Can manage port calls
        ];

        foreach ($permissions as $key => $value) {
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
        DROP TABLE public.permission;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
