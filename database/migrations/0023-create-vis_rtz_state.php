<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vis_rtz_state
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vis_rtz_state_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryInsertVisRtzState = <<<EOT
            INSERT INTO public.vis_rtz_state (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;
        $db->query(
            $queryInsertVisRtzState,
            "CALCULATED_SCHEDULE_NOT_FOUND",
            1,
            1
        );
        $db->query(
            $queryInsertVisRtzState,
            "SYNC_WITH_ETA_FOUND",
            1,
            1
        );
        $db->query(
            $queryInsertVisRtzState,
            "SYNC_WITHOUT_ETA_FOUND",
            1,
            1
        );
        $db->query(
            $queryInsertVisRtzState,
            "SYNC_NOT_FOUND_CAN_BE_ADDED",
            1,
            1
        );
        $db->query(
            $queryInsertVisRtzState,
            "SYNC_NOT_FOUND_CAN_NOT_BE_ADDED",
            1,
            1
        );
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vis_rtz_state;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
