<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.api_key_allowed_timestamp;
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.api_key_allowed_timestamp
        (
            id bigserial NOT NULL,
            api_key_id INT NOT NULL,
            state_id INT NOT NULL REFERENCES public.timestamp_state(id),
            time_type_id INT NOT NULL REFERENCES public.timestamp_time_type(id),
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT api_key_timestamp_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
