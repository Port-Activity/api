<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();

        $queryDefaultValues = <<<EOT
            INSERT INTO public.setting (name, value, created_by, modified_by)
            VALUES (?, ?, ?, ?)
EOT;

        $settings = [];
        $settings["queue_travel_duration_to_berth"] = "PT30M";
        $settings["queue_rta_window_duration"] = "PT30M";
        $settings["queue_laytime_buffer_duration"] = "PT30M";
        $settings["port_operator_emails"] = "";

        foreach ($settings as $k => $v) {
            $db->query(
                $queryDefaultValues,
                $k,
                $v,
                1,
                1
            );
        }

        return true;
    },
    function () {
        $db = Connection::get();

        $query = <<<EOT
            DELETE FROM public.setting WHERE name IN (
                'queue_travel_duration_to_berth',
                'queue_rta_window_duration',
                'queue_laytime_buffer_duration',
                'port_operator_emails')
EOT;

        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
