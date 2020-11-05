<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $query = <<<EOT
        CREATE TABLE public.nomination_status
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            readable_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT nomination_status_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX nomination_status_name_readable_name_idx
        ON public.nomination_status (name, readable_name);
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.nomination_status (name, readable_name, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $statuses = [
            "open" => "Open"
            ,"reserved" => "Reserved"
            ,"expired" => "Expired"
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
        DROP TABLE public.nomination_status;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
