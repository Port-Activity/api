<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.sea_chart_marker_type
        (
            id bigserial NOT NULL,
            name text NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT sea_chart_marker_type_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryDefaultMarkerTypes = <<<EOT
            INSERT INTO public.sea_chart_marker_type (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;
        $db->query(
            $queryDefaultMarkerTypes,
            "timeline",
            1,
            1
        );
        $db->query(
            $queryDefaultMarkerTypes,
            "tug",
            1,
            1
        );
        $db->query(
            $queryDefaultMarkerTypes,
            "other",
            1,
            1
        );

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.sea_chart_marker_type;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
