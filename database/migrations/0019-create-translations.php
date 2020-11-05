<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.translations
        (
            id bigserial NOT NULL,
            namespace text COLLATE pg_catalog."default" NOT NULL,
            language text COLLATE pg_catalog."default" NOT NULL,
            key text COLLATE pg_catalog."default" NOT NULL,
            value text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT translations_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_translations UNIQUE (namespace, language, key)
        );
EOT;
        $db->query($query);
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.translations;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
