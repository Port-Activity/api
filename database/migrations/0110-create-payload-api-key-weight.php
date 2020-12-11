<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.payload_key_api_key_weight
        (
            id bigserial NOT NULL,
            payload_key text COLLATE pg_catalog."default" NOT NULL,
            api_key_id bigserial NOT NULL REFERENCES public.api_key(id) ON DELETE CASCADE,
            weight integer NOT NULL DEFAULT 0,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT payload_key_api_key_weight_pkey PRIMARY KEY (id),
            UNIQUE (payload_key, api_key_id)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX payload_key_api_key_weight_payload_key_api_key_id_weight_idx
        ON public.payload_key_api_key_weight (payload_key, api_key_id, weight);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.payload_key_api_key_weight;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
