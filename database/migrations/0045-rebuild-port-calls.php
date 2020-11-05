<?php
namespace SMA\PAA\DB;

use SMA\PAA\SERVICE\PortCallService;

$migrate = new Migrate(
    __FILE__,
    function () {
        //note: to fix port calls you have run 0045-rebuild-port-calls.sh manually
        return true;
    },
    function () {
        return true;
    }
);

$migrate->migrateOrRevert();
