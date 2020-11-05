<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vis_message
        (
            id bigserial NOT NULL,
            time timestamp with time zone NOT NULL,
            from_service_id text COLLATE pg_catalog."default" NOT NULL,
            to_service_id text COLLATE pg_catalog."default" NOT NULL,
            message_id text COLLATE pg_catalog."default" NOT NULL,
            message_type text COLLATE pg_catalog."default" NOT NULL,
            payload jsonb,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vis_message_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX vis_message_time_from_to_service_id_message_id_type_idx
        ON public.vis_message (time, from_service_id, to_service_id, message_id, message_type);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vis_message;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
