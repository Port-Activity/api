<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.decision
        (
            id bigserial NOT NULL,
            type text COLLATE pg_catalog."default" NOT NULL,
            status text COLLATE pg_catalog."default" NOT NULL DEFAULT 'open',
            notification_id bigint REFERENCES public.notification(id) ON DELETE CASCADE,
            port_call_master_id text COLLATE pg_catalog."default",
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT decision_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        // phpcs:disable
        $query = <<<EOT
        CREATE INDEX decision_type_status_notification_id_port_call_master_id_idx
        ON public.decision (type, status, notification_id, port_call_master_id);
EOT;
        // phpcs:enable
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.decision;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
