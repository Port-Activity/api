<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        // phpcs:disable
        $query = <<<EOT
        CREATE TABLE public.decision_item
        (
            id bigserial NOT NULL,
            decision_id bigint NOT NULL REFERENCES public.decision(id) ON DELETE CASCADE,
            label text COLLATE pg_catalog."default" NOT NULL,
            response_name text COLLATE pg_catalog."default",
            response_type text COLLATE pg_catalog."default",
            response_options jsonb NOT NULL DEFAULT '{"Accept":{"type":"positive"},"Reject":{"type":"negative"}}'::jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT decision_item_pkey PRIMARY KEY (id)
        );
EOT;
        // phpcs:enable
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX decision_item_decision_id_label_response_name_response_type_idx
        ON public.decision_item (decision_id, label, response_name, response_type);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.decision_item;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
