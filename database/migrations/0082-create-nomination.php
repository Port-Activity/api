<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.nomination
        (
            id bigserial NOT NULL,
            company_name text COLLATE pg_catalog."default" NOT NULL,
            email text COLLATE pg_catalog."default" NOT NULL,
            imo integer NOT NULL,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            nomination_status_id serial NOT NULL REFERENCES public.nomination_status(id) ON DELETE NO ACTION,
            window_start timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            window_end timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT nomination_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX nomination_company_name_email_imo_vessel_name_nomination_status_id_window_start_window_end_created_by_idx
        ON public.nomination (company_name, email, imo, vessel_name, nomination_status_id, window_start, window_end, created_by);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.nomination;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
