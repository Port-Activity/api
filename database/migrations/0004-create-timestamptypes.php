<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamptype
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamptype_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryInsertTimestampType = <<<EOT
            INSERT INTO public.timestamptype (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;
        $db->query(
            $queryInsertTimestampType,
            "Port call ETA from vessel",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampType,
            "Port call ATA at berth",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampType,
            "Port call ETD from pilot order for outbound traffic",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampType,
            "Port call ATD from port",
            1,
            1
        );
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamptype;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
