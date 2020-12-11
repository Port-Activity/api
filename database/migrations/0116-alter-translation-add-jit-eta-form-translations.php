<?php
namespace SMA\PAA\DB;

// phpcs:disable
$strings["[EMAIL BERTH BLOCK] Berth"] = "Berth";
$strings["[EMAIL BERTH BLOCK] block"] = "block";
$strings["[EMAIL BERTH BLOCK] shifted"] = "shifted";
$strings["[EMAIL BERTH BLOCK] has been shifted due to laytime change"] = "has been shifted due to laytime change";
$strings["[EMAIL BERTH BLOCK] New berth block time"] = "New berth block time";
$strings["[EMAIL SLOT REQUEST] Slot request"] = "Slot request";
$strings["[EMAIL SLOT REQUEST] offer"] = "offer";
$strings["[EMAIL SLOT REQUEST] confirmation"] = "confirmation";
$strings["[EMAIL SLOT REQUEST] nomination not found"] = "nomination not found";
$strings["[EMAIL SLOT REQUEST] free slot not available"] = "free slot not available";
$strings["[EMAIL SLOT REQUEST] updated by port"] = "updated by port";
$strings["[EMAIL SLOT REQUEST] cancellation confirmation"] = "cancellation confirmation";
$strings["[EMAIL SLOT REQUEST] cancelled by port"] = "cancelled by port";
$strings["[EMAIL SLOT REQUEST] Please send your JIT ETA to outer port area based on RTA window given by port."] = "Please send your JIT ETA to outer port area based on RTA window given by port.";
$strings["[EMAIL SLOT REQUEST] Your JIT ETA to outer port area has been accepted."] = "Your JIT ETA to outer port area has been accepted.";
$strings["[EMAIL SLOT REQUEST] Port is unable to find nomination for your slot request."] = "Port is unable to find nomination for your slot request.";
$strings["[EMAIL SLOT REQUEST] Port is unable to find free slot for your request."] = "Port is unable to find free slot for your request.";
$strings["[EMAIL SLOT REQUEST] Port has updated your JIT ETA."] = "Port has updated your JIT ETA.";
$strings["[EMAIL SLOT REQUEST] Your slot request has been cancelled by your request."] = "Your slot request has been cancelled by your request.";
$strings["[EMAIL SLOT REQUEST] Your slot request has been cancelled by port."] = "Your slot request has been cancelled by port.";
$strings["[EMAIL SLOT REQUEST] To view and update your slot request please use the link below."] = "To view and update your slot request please use the link below.";
$strings["[EMAIL SLOT REQUEST] This link is valid until:"] = "This link is valid until:";
$strings["[EMAIL LIVE ETA ALERT] Live ETA alert for"] = "Live ETA alert for";
$strings["[EMAIL LIVE ETA ALERT] Ship"] = "Ship";
$strings["[EMAIL LIVE ETA ALERT] JIT ETA differs from Live ETA"] = "JIT ETA differs from Live ETA";
$strings["[EMAIL LIVE ETA ALERT] JIT ETA:"] = "JIT ETA:";
$strings["[EMAIL LIVE ETA ALERT] Live ETA:"] = "Live ETA:";
// phpcs:enable

$migrate = new Migrate(
    __FILE__,
    function () use ($strings) {
        $db = Connection::get();
        $query = <<<EOT
            INSERT INTO public.translation (namespace, language, key, value, created_by, modified_by)
            VALUES(?, ?, ?, ?, ?, ?);
EOT;

        $languages = ["en"];
        $namespaces = ["gavle", "rauma"];

        foreach ($languages as $language) {
            foreach ($namespaces as $namespace) {
                foreach ($strings as $k => $v) {
                    $db->query(
                        $query,
                        $namespace,
                        $language,
                        $k,
                        $v,
                        1,
                        1
                    );
                }
            }
        }

        return true;
    },
    function () use ($strings) {
        $keys = "'" . implode("','", array_keys($strings)) . "'";
        $db = Connection::get();
        $query = "DELETE FROM public.translation WHERE key IN (" . $keys . ");";
        $db->query($query);

        return true;
    }
);

$migrate->migrateOrRevert();
