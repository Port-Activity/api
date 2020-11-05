<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $query = <<<EOT
        CREATE INDEX ON logistics_timestamp((payload->>'front_license_plates'));
        EOT;
        $db->query($query);
        $query = <<<EOT
        CREATE INDEX ON logistics_timestamp((payload->>'rear_license_plates'));
        EOT;
        $db->query($query);
        return true;
    },
    function () {
        return true;
    }
);

$migrate->migrateOrRevert();
