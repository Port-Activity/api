<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.notification
        (
            id bigserial NOT NULL,
            type text COLLATE pg_catalog."default" NOT NULL,
            message text COLLATE pg_catalog."default" NOT NULL,
            ship_imo text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT notification_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.notification;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
