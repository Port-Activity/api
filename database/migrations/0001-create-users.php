<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\AuthService as Auth;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.user
        (
            id serial NOT NULL,
            email text COLLATE pg_catalog."default" NOT NULL,
            password_hash text COLLATE pg_catalog."default" NOT NULL,
            first_name text COLLATE pg_catalog."default" NOT NULL,
            last_name text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT user_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_email UNIQUE (email)
        );
EOT;
        $db->query($query);

        $auth = new Auth();
        $queryDefaultUser = <<<EOT
            INSERT INTO public.user (email, password_hash, first_name, last_name, created_by, modified_by)
            VALUES (?, ?, ?, ?, ?, ?)
EOT;
        $db->query(
            $queryDefaultUser,
            "demo@sma",
            $auth->hash(getenv("PAA_DEFAULT_USER_PASSWORD")),
            "Demo",
            "SMA",
            1,
            1
        );
        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.user;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
