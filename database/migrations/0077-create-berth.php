<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.berth
        (
            id bigserial NOT NULL,
            code text COLLATE pg_catalog."default" NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            nominatable bool NOT NULL DEFAULT 'no',
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT berth_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX berth_name_code_nominatable_idx
        ON public.berth (name, code, nominatable);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.berth;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
