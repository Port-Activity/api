<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.setting
        (
            id bigserial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            value text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT setting_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryDefaultValues = <<<EOT
            INSERT INTO public.setting (name, value, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $db->query(
            $queryDefaultValues,
            "activity_module",
            "enabled",
            1,
            1
        );

        $db->query(
            $queryDefaultValues,
            "logistics_module",
            "enabled",
            1,
            1
        );

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.setting;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
