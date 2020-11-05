<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp_state_type
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_state_type_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryInserttimestampStateType = <<<EOT
            INSERT INTO public.timestamp_state_type (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;
        $db->query(
            $queryInserttimestampStateType,
            "Location",
            1,
            1
        );
        $db->query(
            $queryInserttimestampStateType,
            "Service",
            1,
            1
        );
        $db->query(
            $queryInserttimestampStateType,
            "Administration",
            1,
            1
        );

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp_state_type;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
