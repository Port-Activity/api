<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.timestamp_definition
        (
            id serial NOT NULL,
            name text COLLATE pg_catalog."default" NOT NULL,
            time_type_id serial NOT NULL REFERENCES public.timestamp_time_type ON DELETE CASCADE,
            state_id serial NOT NULL REFERENCES public.timestamp_state ON DELETE CASCADE,
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT timestamp_definition_pkey PRIMARY KEY (id),
            CONSTRAINT uniq_time_type_state UNIQUE (time_type_id, state_id)
        );
EOT;
        $db->query($query);

        unset($query);
        $query = <<<EOT
        CREATE INDEX timestamp_definition_name_idx
        ON public.timestamp_definition (name);
EOT;

        $queryDefaultValues = <<<EOT
            INSERT INTO public.timestamp_definition (name, time_type_id, state_id, created_by, modified_by)
            SELECT ?, t.id, s.id, ?, ?
            FROM public.timestamp_time_type t, public.timestamp_state s
            WHERE t.name = ?
            AND s.name = ?;

EOT;

        $timestampDefinitions = [];
        $timestampDefinitions = [
            // Traffic area arrival
            ["Estimated", "Arrival_Vessel_TrafficArea"]
            ,["Actual", "Arrival_Vessel_TrafficArea"]
            // LOC arrival
            ,["Actual", "Arrival_Vessel_LOC"]
            // Anchorage area arrival
            ,["Actual", "Arrival_Vessel_AnchorageArea"]
            // Pilotage
            ,["Estimated", "Pilotage_Reguested"]
            ,["Estimated", "Pilotage_Confirmed"]
            ,["Actual", "Pilotage_Commenced"]
            ,["Actual", "Pilotage_Completed"]
            // Port area arrival
            ,["Estimated", "Arrival_Vessel_PortArea"]
            ,["Recommended", "Arrival_Vessel_PortArea"]
            ,["Planned", "Arrival_Vessel_PortArea"]
            ,["Actual", "Arrival_Vessel_PortArea"]
            // Berth arrival
            ,["Actual", "Arrival_Vessel_Berth"]
            // Cargo Op
            ,["Actual", "CargoOp_Commenced"]
            ,["Estimated", "CargoOp_Completed"]
            ,["Actual", "CargoOp_Completed"]
            // Berth departue
            ,["Estimated", "Departure_Vessel_Berth"]
            ,["Actual", "Departure_Vessel_Berth"]
            // Port area departue
            ,["Actual", "Departure_Vessel_PortArea"]
        ];

        foreach ($timestampDefinitions as $timestampDefinition) {
            $name = $timestampDefinition[0] . " " . str_replace("_", " ", $timestampDefinition[1]);
            $db->query(
                $queryDefaultValues,
                $name,
                1,
                1,
                $timestampDefinition[0],
                $timestampDefinition[1]
            );
        }

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.timestamp_definition;
EOT;
        $db->query($query);
        return true;
    }
);

$migrate->migrateOrRevert();
