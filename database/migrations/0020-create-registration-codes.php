<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.registration_codes
        (
            id bigserial NOT NULL,
            code text COLLATE pg_catalog."default" NOT NULL,
            enabled boolean NOT NULL,
            role text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT registration_codes_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_registration_codes UNIQUE (code)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.registration_codes;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
