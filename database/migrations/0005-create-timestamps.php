<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp
        (
            id bigserial NOT NULL,
            imo integer NOT NULL,
            vesselname text COLLATE pg_catalog."default" NOT NULL,
            type_id serial NOT NULL REFERENCES public.timestamptype(id),
            ts timestamp with time zone  NOT NULL,
            payload jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
