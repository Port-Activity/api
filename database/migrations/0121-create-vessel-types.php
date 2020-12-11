<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE TABLE public.vessel_type
        (
            id bigserial NOT NULL,
            name text NOT NULL,
            sea_chart_marker_type_id integer NOT NULL REFERENCES public.sea_chart_marker_type(id),
            created_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            created_by integer NOT NULL,
            modified_at timestamp with time zone  NOT NULL DEFAULT CURRENT_DATE,
            modified_by integer NOT NULL,
            CONSTRAINT vessel_type_pkey PRIMARY KEY (id)
        );
EOT;
        $db->query($query);

        $insertYellowMarkerQuery = <<<EOT
            INSERT INTO public.sea_chart_marker_type (name, created_by, modified_by)
            VALUES (?, ?, ?)
EOT;

        $db->query(
            $insertYellowMarkerQuery,
            "vessel_yellow",
            1,
            1
        );

        $insertVesselTypeQuery = <<<EOT
            INSERT INTO public.vessel_type (name, sea_chart_marker_type_id, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;
        $db->query($insertVesselTypeQuery, "Unknown Vessel Type", 1, 1, 1);

        $db->query($insertVesselTypeQuery, "General Cargo", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Bulk Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Container Ship", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Ro-Ro/Vehicles Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Reefer", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Aggregates Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Cement Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Ore Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Livestock Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "OBO Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Heavy Load Carrier", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Barge", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Inland Cargo", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Cargo", 5, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Cargo", 5, 1, 1);

        $db->query($insertVesselTypeQuery, "Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Oil Products Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Crude Oil Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Oil/Chemical Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Chemical Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "LPG Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "LNG Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Bunkering Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Asphalt/Bitumen Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Water Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Inland Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Tanker", 8, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Tanker", 8, 1, 1);

        $db->query($insertVesselTypeQuery, "Passenger Ship", 2, 1, 1);
        $db->query($insertVesselTypeQuery, "Ro-Ro/Passenger Ship", 2, 1, 1);
        $db->query($insertVesselTypeQuery, "Inland Passenger Ship", 2, 1, 1);
        $db->query($insertVesselTypeQuery, "Passenger/Cargo Ship", 2, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Passenger Ship", 2, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Passenger Ship", 2, 1, 1);

        $db->query($insertVesselTypeQuery, "High Speed Craft", 10, 1, 1);

        $db->query($insertVesselTypeQuery, "Tug", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Pusher Tug", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Tug/Supply Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Tug", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Pilot Boat", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Supply Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Service Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Offshore Structure", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Dredger", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Research/Survey Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Crew Boat", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Patrol Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Cable Layer", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Landing Craft", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Offshore Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Anchor Handling Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Platform", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Floating Storage/Production", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Floating Crane", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Drill Ship", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Search & Rescue", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Pollution Control Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Icebreaker", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Fire Fighting Vessel", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Training Ship", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Inland Tug", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Craft", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Military Ops", 3, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Tug / Special Craft", 3, 1, 1);

        $db->query($insertVesselTypeQuery, "Fishing Vessel", 9, 1, 1);
        $db->query($insertVesselTypeQuery, "Fish Carrier", 9, 1, 1);
        $db->query($insertVesselTypeQuery, "Trawler", 9, 1, 1);
        $db->query($insertVesselTypeQuery, "Special Fishing Vessel", 9, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Fishing", 9, 1, 1);

        $db->query($insertVesselTypeQuery, "Yacht", 4, 1, 1);
        $db->query($insertVesselTypeQuery, "Sailing Vessel", 4, 1, 1);
        $db->query($insertVesselTypeQuery, "Other Pleasure Craft", 4, 1, 1);

        return true;
    },
    function () {
        $db = Connection::get();
        $query = <<<EOT
        DROP TABLE public.vessel_type;
EOT;
        $db->query($query);

        $deleteYellowMarkerQuery = <<<EOT
            DELETE FROM public.sea_chart_marker_type WHERE name = 'vessel_yellow'
EOT;
        $db->query($deleteYellowMarkerQuery);
        return true;
    }
);

$migrate->migrateOrRevert();
