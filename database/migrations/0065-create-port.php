<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.port
        (
            id bigserial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            service_id text COLLATE pg_catalog."default" NOT NULL,
            whitelist_in bool NOT NULL DEFAULT 'yes',
            whitelist_out bool NOT NULL DEFAULT 'yes',
            locodes jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT port_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX port_name_service_id_whitelist_in_whitelist_out_idx
        ON public.port (name, service_id, whitelist_in, whitelist_out);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.port;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
