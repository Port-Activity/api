<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $query = <<<EOT
        CREATE TABLE public.slot_reservation_status
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            readable_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT slot_reservation_status_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX slot_reservation_status_name_readable_name_idx
        ON public.slot_reservation_status (name, readable_name);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.slot_reservation_status (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $statuses = [
            "requested" => "Requested" // ETA information sent by vessel
            ,"no_nomination" => "No nomination found" // Cannot find nomination for slot request
            ,"no_free_slot" => "No free slots" // Cannot find free slot for slot request
            ,"offered" => "Offered" // RTA window offered to vessel
            ,"accepted" => "Accepted" // JIT ETA sent by vessel
            ,"updated" => "Updated by port" // JIT ETA updated by port
            ,"cancelled_by_vessel" => "Cancelled by vessel" // Reservation cancelled by vessel
            ,"cancelled_by_port" => "Cancelled by port" // Reservation cancelled by port
            ,"completed" => "Completed" // Reservation completed
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
        DROP TABLE public.slot_reservation_status;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
