<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.api_key
        (
            id bigserial NOT NULL,
            key text COLLATE pg_catalog."default" NOT NULL,
            is_active boolean NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT api_key_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_api_key UNIQUE (key)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.api_key;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
