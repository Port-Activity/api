<?php
namespace SMA\PAA\DB;

require_once __DIR__ . "/../src/lib/init.php";

echo "Starting migration process\n";
echo "Checking migration tables exists... ";
$last = Migrate::last();
echo "OK\n";

echo $last ? "Last migration is " . $last . "\n" : "No migration yet\n";

$files = scandir(__DIR__ . "/migrations");
foreach ($files as $file) {
    if ($file !== "." && $file !== "..") {
        echo "Checking " . $file . "... ";
        if (!$last || strcmp($last, $file) < 0) {
            require __DIR__ . "/migrations/$file";
            echo "OK\n";
        } else {
            echo "SKIP\n";
        }
    }
}
