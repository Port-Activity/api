<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vis_vessel
        (
            id bigserial NOT NULL,
            imo integer NOT NULL,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            service_id text COLLATE pg_catalog."default" NOT NULL,
            service_url text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vis_vessel_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_vessel_imo_vessel_name_service_id_idx
        ON public.vis_vessel (imo, vessel_name, service_id);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vis_vessel;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
