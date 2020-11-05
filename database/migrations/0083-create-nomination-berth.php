<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.nomination_berth
        (
            id bigserial NOT NULL,
            nomination_id bigserial NOT NULL REFERENCES public.nomination(id) ON DELETE CASCADE,
            berth_id bigserial NOT NULL REFERENCES public.berth(id) ON DELETE CASCADE,
            berth_priority integer NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT nomination_berth_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_nomination_berth UNIQUE (nomination_id, berth_id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX nomination_berth_nomination_id_berth_id_berth_priority_idx
        ON public.nomination_berth (nomination_id, berth_id, berth_priority);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.nomination_berth;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
