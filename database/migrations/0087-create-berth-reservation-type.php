<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $query = <<<EOT
        CREATE TABLE public.berth_reservation_type
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            readable_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT berth_reservation_type_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX berth_reservation_type_name_readable_name_idx
        ON public.berth_reservation_type (name, readable_name);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.berth_reservation_type (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $statuses = [
            "vessel_reserved" => "Reserved for vessel"
            ,"port_blocked" => "Blocked by port"
        ];

        foreach ($statuses as $key => $value) {
            $db->query(
                $queryDefaultValues,
                $key,
                $value,
                1,
                1
            );
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.berth_reservation_type;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
