<?php
namespace SMA\PAA;

use Exception;
use SMA\PAA\SERVICE\LogService;
use SMA\PAA\TOOL\Timer;

$executionStartTime = microtime(true);
require __DIR__ . "/init.php";
require __DIR__ . "/routes.php";

// OPTIONS HANDLING
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit(0);
}

// Note: quick debug add lines below to quicly get timer in a code without any fancy tools
// Timer::get()->mark("start");

$server = new Server($_SERVER);
$session = new Session();
try {
    $session->start($server);

    $app = new App($server);

    $app->setAliases(routes());

    $error = false;
    try {
        echo json_encode($app->run());
    } catch (InvalidParameterException $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    } catch (AuthenticationException $e) {
        http_response_code(403);
        echo json_encode(["error" => $e->getMessage()]);
    } catch (SessionException $e) {
        http_response_code(440);
        echo json_encode(["error" => $e->getMessage()]);
    } catch (InvalidOperationException $e) {
        http_response_code(400);
        echo json_encode(["error" => $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(400);
        $error = $e;
    }

    $executionEndTime = microtime(true);
    $executionTime = $executionEndTime - $executionStartTime;

    $log = new LogService();
    $log->log(new Session(), new Server(), new Response(), $executionTime);

    if ($error) {
        echo json_encode(["error" => $error->getMessage()]);
        $expandedError = [
            "message" => $error->getMessage(),
            "stack" => $error->getTraceAsString(),
            "debug_trace" => debug_backtrace()
        ];
        trigger_error(print_r($expandedError, true), E_USER_ERROR);
    }
} catch (SessionException $e) {
    http_response_code(440);
    echo json_encode(["error" => $e->getMessage()]);
}

Timer::get()->mark("end");

// var_dump(Timer::get()->results());
