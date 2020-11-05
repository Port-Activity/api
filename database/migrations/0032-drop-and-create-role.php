<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.role;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE TABLE public.role
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            readable_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT role_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX role_name_idx
        ON public.role (name);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.role (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $roles = [
            "admin" => "Administrator"
            ,"second_admin" => "Second administrator"
            ,"first_user" => "First user"
            ,"user" => "User"
            ,"inactive_user" => "Inactive user"
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
        DROP TABLE public.role;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE TABLE public.role
        (
            id bigserial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            user_id serial NOT NULL REFERENCES public.user(id) ON DELETE CASCADE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT role_pkey PRIMARY KEY (id),
            UNIQUE (name, user_id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        INSERT INTO public.role
        SELECT * FROM public.backup_role;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
