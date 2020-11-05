<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.backup_role
        (
            id bigserial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            user_id serial NOT NULL REFERENCES public.user(id) ON DELETE CASCADE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT backup_role_pkey PRIMARY KEY (id),
            UNIQUE (name, user_id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        INSERT INTO public.backup_role
        SELECT * FROM public.role;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.backup_role;
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
