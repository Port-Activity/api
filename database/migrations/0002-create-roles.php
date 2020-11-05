<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
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

        $queryDefaultRole = <<<EOT
            INSERT INTO public.role (name, user_id, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $db->query(
            $queryDefaultRole,
            "admin",
            1,
            1,
            1
        );
        $db->query(
            $queryDefaultRole,
            "second_admin",
            1,
            1,
            1
        );
        $db->query(
            $queryDefaultRole,
            "first_user",
            1,
            1,
            1
        );
        $db->query(
            $queryDefaultRole,
            "user",
            1,
            1,
            1
        );
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.role;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
