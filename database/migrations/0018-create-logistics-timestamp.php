<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.logistics_timestamp
        (
            id bigserial NOT NULL,
            time timestamp with time zone NOT NULL,
            checkpoint text COLLATE pg_catalog."default" NOT NULL,
            direction text COLLATE pg_catalog."default" NOT NULL,
            payload jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT logistics_timestamp_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX logistics_timestamp_time_idx
        ON public.logistics_timestamp (time);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.logistics_timestamp;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
