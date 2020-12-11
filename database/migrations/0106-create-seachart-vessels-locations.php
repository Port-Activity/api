<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.sea_chart_vessel_location
        (
            id bigserial NOT NULL,
            imo integer,
            mmsi integer,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            sea_chart_marker_type_id integer NOT NULL REFERENCES public.sea_chart_marker_type(id),
            latitude real NOT NULL,
            longitude real NOT NULL,
            heading_degrees real,
            speed_knots real,
            location_timestamp timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT sea_chart_vessel_location_pkey PRIMARY KEY (id),
            FOREIGN KEY (imo) REFERENCES public.vessel(imo)
        );
EOT;
        $db->query($query);

        $indexes = [
            "imo"
            ,"mmsi"
            ,"vessel_name"
            ,"sea_chart_marker_type_id"
            ,"latitude"
            ,"longitude"
            ,"heading_degrees"
            ,"speed_knots"
            ,"location_timestamp"
        ];

        foreach ($indexes as $index) {
            $query = <<<EOT
            CREATE INDEX sea_chart_vessel_location_${index}_idx
            ON public.sea_chart_vessel_location (${index});
    EOT;
            $db->query($query);
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.sea_chart_vessel_location;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
