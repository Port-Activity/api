<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.port_call_payload_key_weight
        (
            id bigserial NOT NULL,
            port_call_id bigserial NOT NULL REFERENCES public.port_call(id) ON DELETE CASCADE,
            payload_key text COLLATE pg_catalog."default" NOT NULL,
            weight integer NOT NULL DEFAULT 0,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT port_call_payload_key_weight_pkey PRIMARY KEY (id),
            UNIQUE (port_call_id, payload_key)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX port_call_payload_key_weight_port_call_id_payload_key_weight_idx
        ON public.port_call_payload_key_weight (port_call_id, payload_key, weight);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.port_call_payload_key_weight;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
