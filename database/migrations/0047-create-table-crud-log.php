<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.crud_log
        (
            id bigserial NOT NULL,
            table_name text,
            data jsonb,
            created_at timestamp with time zone NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT crud_log_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
            DROP TABLE public.crud_log;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
