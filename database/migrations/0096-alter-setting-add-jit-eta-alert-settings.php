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
        $settings["queue_live_eta_alert_buffer_duration"] = "PT1H";
        $settings["queue_live_eta_alert_delay_duration"] = "PT2H";

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
                'queue_live_eta_alert_buffer_duration',
                'queue_live_eta_alert_delay_duration')
EOT;

        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
