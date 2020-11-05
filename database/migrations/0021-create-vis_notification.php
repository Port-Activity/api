<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vis_notification
        (
            id bigserial NOT NULL,
            time timestamp with time zone NOT NULL,
            from_service_id text COLLATE pg_catalog."default" NOT NULL,
            payload jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vis_notification_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_notification_time_from_service_id_idx
        ON public.vis_notification (time, from_service_id);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vis_notification;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
