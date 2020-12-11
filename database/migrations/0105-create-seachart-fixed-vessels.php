<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.sea_chart_fixed_vessel
        (
            id bigserial NOT NULL,
            imo integer,
            mmsi integer NOT NULL,
            sea_chart_marker_type_id integer NOT NULL REFERENCES public.sea_chart_marker_type(id),
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT sea_chart_fixed_vessel_pkey PRIMARY KEY (id),
            FOREIGN KEY (imo) REFERENCES public.vessel(imo)
        );
EOT;
        $db->query($query);

        $indexes = [
            "imo"
            ,"mmsi"
            ,"sea_chart_marker_type_id"
            ,"vessel_name"
        ];

        foreach ($indexes as $index) {
            $query = <<<EOT
            CREATE INDEX sea_chart_fixed_vessel_${index}_idx
            ON public.sea_chart_fixed_vessel (${index});
    EOT;
            $db->query($query);
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.sea_chart_fixed_vessel;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
