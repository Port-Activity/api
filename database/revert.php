<?php
namespace SMA\PAA\DB;

require "src/lib/init.php";

echo "Starting revert process\n";
echo "Checking migration tables exists... ";
$last = Migrate::last();
echo "OK\n";
if ($last) {
    echo "Last migration is " . $last . "... ";

    require "database/migrations/$last";
    echo "REVERTED\n";
} else {
    echo "Nothing to revert\n";
}
