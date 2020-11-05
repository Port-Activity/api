<?php
namespace SMA\PAA;

header('Access-Control-Allow-Methods: POST,GET,DELETE,PUT,OPTIONS');
header(
    'Access-Control-Allow-Headers: '
    . 'Authorization, Origin, X-Requested-With, Content-Type, Accept, Credentials, Cache-Control, ClientTimeZone'
);
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Security-Policy: default-src \'self\'');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

require_once __DIR__ . "/../../vendor/autoload.php";
require __DIR__ . "/autoload.php";
require __DIR__ . "/error_functions.php";

if (!getenv("SKIP_LOCAL_INIT") && file_exists(__DIR__ . "/init_local.php")) {
    require(__DIR__ . "/init_local.php");
}

error_reporting(E_ALL);
set_exception_handler("SMA\\PAA\\exceptionHandler");
set_error_handler("SMA\\PAA\\errorHandler");
register_shutdown_function("SMA\\PAA\\shutdownHandler");


// TODO: Review this, we are playing with locks which may cause problems.
//       Can we really make migrations on fly?
//       We need to be very careful when creating migrations and consider:
//       - multiple containers are runing
//       - container with previous version is still running/executing after latest migration
$filename = "/tmp/first-run";
$lock_filename = "/tmp/first-run.lock";
if (file_exists($filename)) {
    ob_start();
    $fp = fopen($lock_filename, "w+");
    if (flock($fp, LOCK_EX)) {
        if (file_exists($filename)) {
            require(__DIR__ . "/../../database/create.php");
            function_exists("apache_setenv") && apache_setenv("PAA_MIGRATE", "1");
            putenv("PAA_MIGRATE=1");
            require(__DIR__ . "/../../database/migrate.php");
            function_exists("apache_setenv") && apache_setenv("PAA_MIGRATE", "0");
            putenv("PAA_MIGRATE=0");
            unlink($filename);
        }
        flock($fp, LOCK_UN);
    } else {
        echo "Failed to get lock...\n";
    }
    fclose($fp);
    $output = ob_get_clean();
    syslog(LOG_INFO, $output);
}
