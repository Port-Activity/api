<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.pushnotificationtoken
        (
            id bigserial NOT NULL,
            user_id serial NOT NULL REFERENCES public.user(id) ON DELETE CASCADE,
            installation_id text COLLATE pg_catalog."default" NOT NULL,
            platform text COLLATE pg_catalog."default" NOT NULL,
            token text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT push_notification_token_pkey PRIMARY KEY (id),
            UNIQUE (user_id, installation_id, platform)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.pushnotificationtoken;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
