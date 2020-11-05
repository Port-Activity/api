<?php
namespace SMA\PAA\DB;

$migrate = new Migrate(
    __FILE__,
    function () {
        $db = Connection::get();
        $db->query("ALTER TABLE pushnotificationtoken RENAME TO push_notification_token;");
        return true;
    },
    function () {
        $db = Connection::get();
        $db->query("ALTER TABLE push_notification_token RENAME TO pushnotificationtoken;");
        return true;
    }
);

$migrate->migrateOrRevert();
