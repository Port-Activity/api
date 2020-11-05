<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        // phpcs:disable
        $query = <<<EOT
        CREATE TABLE public.slot_reservation
        (
            id bigserial NOT NULL,
            nomination_id bigint REFERENCES public.nomination(id) ON DELETE SET NULL,
            berth_id bigint REFERENCES public.berth(id) ON DELETE NO ACTION,
            email text COLLATE pg_catalog."default" NOT NULL,
            imo integer NOT NULL,
            vessel_name text COLLATE pg_catalog."default" NOT NULL,
            loa real NOT NULL,
            beam real NOT NULL,
            draft real NOT NULL,
            laytime interval NOT NULL,
            eta timestamp with time zone NOT NULL,
            rta_window_start timestamp with time zone,
            rta_window_end timestamp with time zone,
            jit_eta timestamp with time zone,
            slot_reservation_status_id serial NOT NULL REFERENCES public.slot_reservation_status(id) ON DELETE NO ACTION,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT slot_reservation_pkey PRIMARY KEY (id)
        );
EOT;
        // phpcs:enable
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX slot_reservation_nomination_id_berth_id_email_imo_vessel_name_eta_rta_window_start_rta_window_end_jit_eta_slot_reservation_status_id_idx
        ON public.slot_reservation (nomination_id, berth_id, email, imo, vessel_name, eta, rta_window_start, rta_window_end, jit_eta, slot_reservation_status_id);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.slot_reservation;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
