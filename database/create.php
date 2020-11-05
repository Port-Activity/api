<?php
require_once __DIR__ . "/../src/lib/init.php";

use SMA\PAA\DB\Connection;

$hostname = getenv('PAA_DB_HOSTNAME');
$database = getenv('PAA_DB_DATABASE');
$databaseAdmin = getenv('PAA_DB_ADMIN_DATABASE');
$port = getenv('PAA_DB_PORT');
$username = getenv('PAA_DB_USERNAME');
$usernameAdmin = getenv('PAA_DB_ADMIN_USERNAME');
$password = getenv('PAA_DB_PASSWORD');
$passwordAdmin = getenv('PAA_DB_ADMIN_PASSWORD');
$db = new Connection(
    Connection::$SINGLETON_WARNING_PASS,
    $hostname,
    $port,
    $databaseAdmin,
    $usernameAdmin,
    $passwordAdmin
);

try {
    $db->query("CREATE USER $username WITH PASSWORD '$password';");
} catch (Exception $e) {
    error_log("User already created");
}
try {
    $db->query("CREATE DATABASE $database;");
} catch (Exception $e) {
    error_log("Database already created");
}
try {
    $db->query("GRANT ALL PRIVILEGES ON DATABASE $database to $username;");
} catch (Exception $e) {
    error_log("Something went wront while granting permissions");
}
