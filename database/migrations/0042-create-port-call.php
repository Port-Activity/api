<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.port_call
        (
            id bigserial NOT NULL,
            imo bigint NOT NULL,
            vessel_name text COLLATE pg_catalog."default",
            status text COLLATE pg_catalog."default" NOT NULL,

            from_port text COLLATE pg_catalog."default",
            to_port text COLLATE pg_catalog."default",
            next_port text COLLATE pg_catalog."default",
            mmsi text COLLATE pg_catalog."default",
            call_sign text COLLATE pg_catalog."default",
            loa real,
            beam real,
            draft real,
            net_weight real,
            gross_weight real,
            nationality text COLLATE pg_catalog."default",

            first_eta timestamp with time zone NOT NULL,
            first_etd timestamp with time zone,
            current_eta timestamp with time zone NOT NULL,
            current_etd timestamp with time zone,
            rta timestamp with time zone,
            current_pta timestamp with time zone,
            current_ptd timestamp with time zone,
            ata timestamp with time zone,
            atd timestamp with time zone,

            is_vis boolean,
            vis_status text COLLATE pg_catalog."default",

            inbound_piloting_status  text COLLATE pg_catalog."default",
            outbound_piloting_status  text COLLATE pg_catalog."default",
            cargo_operations_status  text COLLATE pg_catalog."default",

            created_at timestamp with time zone NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT port_call_pkey PRIMARY KEY (id),
            FOREIGN KEY (imo) REFERENCES public.vessel(imo)
        );
EOT;
        $db->query($query);

        $indexes = [
            "imo"
            ,"status"
            ,"from_port"
            ,"to_port"
            ,"nationality"
            ,"first_eta"
            ,"first_etd"
            ,"current_eta"
            ,"current_etd"
            ,"rta"
            ,"current_pta"
            ,"current_ptd"
            ,"ata"
            ,"atd"
        ];

        foreach ($indexes as $index) {
            $query = <<<EOT
            CREATE INDEX port_call_${index}_idx
            ON public.port_call (${index});
    EOT;
            $db->query($query);
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.port_call;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
