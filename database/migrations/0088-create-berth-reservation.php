<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.berth_reservation
        (
            id bigserial NOT NULL,
            berth_id bigserial NOT NULL REFERENCES public.berth(id) ON DELETE NO ACTION,
            berth_reservation_type_id serial NOT NULL REFERENCES public.berth_reservation_type(id) ON DELETE NO ACTION,
            reservation_start timestamp with time zone NOT NULL,
            reservation_end timestamp with time zone NOT NULL,
            slot_reservation_id bigint REFERENCES public.slot_reservation(id) ON DELETE CASCADE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT berth_reservation_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX berth_reservation_berth_id_berth_reservation_type_id_reservation_start_reservation_end_slot_reservation_id_idx
        ON public.berth_reservation (berth_id, berth_reservation_type_id, reservation_start, reservation_end, slot_reservation_id);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.berth_reservation;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
