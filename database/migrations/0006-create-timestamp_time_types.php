<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp_time_type
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_time_type_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryInsertTimestampTimeType = <<<EOT
            INSERT INTO public.timestamp_time_type (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;
        $db->query(
            $queryInsertTimestampTimeType,
            "Planned",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampTimeType,
            "Estimated",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampTimeType,
            "Actual",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampTimeType,
            "Recommended",
            1,
            1
        );
        $db->query(
            $queryInsertTimestampTimeType,
            "Required",
            1,
            1
        );
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp_time_type;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
