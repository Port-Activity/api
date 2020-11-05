<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        DROP COLUMN type_id;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        DROP TABLE public.timestamptype;
EOT;
        $db->query($query);

        return true;
    },
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

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ADD COLUMN type_id serial;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ETA from vessel')
        WHERE time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Estimated')
        AND state_id = (SELECT id from public.timestamp_state WHERE name = 'Arrival_Vessel_PortArea');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ATA at berth')
        WHERE time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Actual')
        AND state_id = (SELECT id from public.timestamp_state WHERE name = 'Arrival_Vessel_Berth');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET type_id = (
                SELECT id from public.timestamptype WHERE name = 'Port call ETD from pilot order for outbound traffic'
        )
        WHERE time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Estimated')
        AND state_id = (SELECT id from public.timestamp_state WHERE name = 'Departure_Vessel_Berth');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ATD from port')
        WHERE time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Actual')
        AND state_id = (SELECT id from public.timestamp_state WHERE name = 'Departure_Vessel_Berth');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ALTER COLUMN type_id SET NOT NULL,
        ADD CONSTRAINT "timestamptype_id_fkey" FOREIGN KEY (type_id) REFERENCES public.timestamptype(id);
EOT;
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
