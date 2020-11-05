<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ADD COLUMN time_type_id serial,
        ADD COLUMN state_id serial;
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Estimated'),
        state_id = (SELECT id from public.timestamp_state WHERE name = 'Arrival_Vessel_PortArea')
        WHERE type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ETA from vessel');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Actual'),
        state_id = (SELECT id from public.timestamp_state WHERE name = 'Arrival_Vessel_Berth')
        WHERE type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ATA at berth');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Estimated'),
        state_id = (SELECT id from public.timestamp_state
        WHERE name = 'Departure_Vessel_Berth')
        WHERE type_id = (
                SELECT id from public.timestamptype WHERE name = 'Port call ETD from pilot order for outbound traffic'
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        UPDATE public.timestamp
        SET time_type_id = (SELECT id from public.timestamp_time_type WHERE name = 'Actual'),
        state_id = (SELECT id from public.timestamp_state WHERE name = 'Departure_Vessel_Berth')
        WHERE type_id = (SELECT id from public.timestamptype WHERE name = 'Port call ATD from port');
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        ALTER TABLE public.timestamp
        ALTER COLUMN time_type_id SET NOT NULL,
        ALTER COLUMN state_id SET NOT NULL,
        ADD CONSTRAINT "timestamp_time_type_id_fkey"
                FOREIGN KEY (time_type_id) REFERENCES public.timestamp_time_type(id),
        ADD CONSTRAINT "timestamp_state_id_fkey"
                FOREIGN KEY (state_id) REFERENCES public.timestamp_state(id);
EOT;
        $db->query($query);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        ALTER TABLE public.timestamp
        DROP COLUMN time_type_id,
        DROP COLUMN state_id;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
