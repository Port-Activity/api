<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.inbound_vessel
        (
            id bigserial NOT NULL,
            time timestamp with time zone NOT NULL,
            imo integer NOT NULL,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            from_service_id text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT inbound_vessel_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX inbound_vessel_time_imo_vessel_name_from_service_id_idx
        ON public.inbound_vessel (time, imo, vessel_name, from_service_id);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.inbound_vessel;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
