<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vessel
        (
            id bigserial NOT NULL,
            imo integer NOT NULL,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vessel_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vessel_imo_vessel_name_idx
        ON public.vessel (imo, vessel_name);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vessel;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
