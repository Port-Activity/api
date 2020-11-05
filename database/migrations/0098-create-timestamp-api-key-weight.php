<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp_api_key_weight
        (
            id bigserial NOT NULL,
            timestamp_time_type_id serial NOT NULL REFERENCES public.timestamp_time_type(id) ON DELETE CASCADE,
            timestamp_state_id serial NOT NULL REFERENCES public.timestamp_state(id) ON DELETE CASCADE,
            api_key_id bigserial NOT NULL REFERENCES public.api_key(id) ON DELETE CASCADE,
            weight integer NOT NULL DEFAULT 0,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_api_key_weight_pkey PRIMARY KEY (id),
            UNIQUE (timestamp_time_type_id, timestamp_state_id, api_key_id)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX timestamp_api_key_weight_timestamp_time_type_id_timestamp_state_id_api_key_id_weight_idx
        ON public.timestamp_api_key_weight (timestamp_time_type_id, timestamp_state_id, api_key_id, weight);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp_api_key_weight;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
