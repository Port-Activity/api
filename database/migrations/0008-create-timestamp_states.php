<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp_state
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            state_type_id serial NOT NULL REFERENCES public.timestamp_state_type(id),
            description text COLLATE pg_catalog."default" NOT NULL,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_state_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $queryInsertTimestampState = <<<EOT
            INSERT INTO public.timestamp_state (name, description, created_by, modified_by, state_type_id)
            VALUES (?, ?, ?, ?, (SELECT id FROM public.timestamp_state_type WHERE name = ?));
EOT;

        $stateCatalogue = simplexml_load_file(__DIR__ . "/../IALA-211_State_Catalogue.xml");

        foreach ($stateCatalogue->LocationStates as $locationStates) {
            foreach ($locationStates as $locationState) {
                $db->query(
                    $queryInsertTimestampState,
                    $locationState->StateId,
                    $locationState->Description,
                    1,
                    1,
                    "Location"
                );
            }
        }

        foreach ($stateCatalogue->ServiceStates as $serviceStates) {
            foreach ($serviceStates as $serviceState) {
                $db->query(
                    $queryInsertTimestampState,
                    $serviceState->StateId,
                    $serviceState->Description,
                    1,
                    1,
                    "Service"
                );
            }
        }

        foreach ($stateCatalogue->AdministrationStates as $administrationStates) {
            foreach ($administrationStates as $administrationState) {
                $db->query(
                    $queryInsertTimestampState,
                    $administrationState->StateId,
                    $administrationState->Description,
                    1,
                    1,
                    "Administration"
                );
            }
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp_state;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
