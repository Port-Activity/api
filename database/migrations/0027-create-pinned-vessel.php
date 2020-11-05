<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.pinned_vessel
        (
            id bigserial NOT NULL,
            user_id serial NOT NULL REFERENCES public.user(id) ON DELETE CASCADE,
            vessel_ids jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT pinned_vessel_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_pinned_vessel UNIQUE (user_id)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.pinned_vessel;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
