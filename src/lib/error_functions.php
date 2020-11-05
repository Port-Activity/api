<?php
namespace SMA\PAA;

use SMA\PAA\SERVICE\EmailService;

function dontSend($error)
{
    $text = print_r($error, true);
    //TODO: remove this when problem is fixed
    //      it spams email so much that sendgrid free quota is reached
    $pregs = [
        "/Module 'session' already loaded/",
        "/Error while reading line from the server/"
    ];
    foreach ($pregs as $preg) {
        if (preg_match($preg, $text)) {
            error_log("Skipping sending email for error: " . $text);
            return true;
        }
    }
    return false;
}

function exceptionHandler($exception)
{
    $emailTo = getenv("ERROR_EMAIL");
    if ($emailTo && !dontSend($exception)) {
        $service = new EmailService();
        $service->sendEmail(
            $emailTo,
            "Error: Port Activity App (exceptionHandler)",
            print_r($exception, true)
        );
    }
    throw $exception;
}

function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext): bool
{
    $emailTo = getenv("ERROR_EMAIL");
    if (!$emailTo) {
        return false;
    }
    if (dontSend($errstr)) {
        return false;
    }
    $service = new EmailService();
    $service->sendEmail(
        $emailTo,
        "Error: Port Activity App (errorHandler)",
        $errstr
    );
    return false;
}

function shutdownHandler()
{
    $emailTo = getenv("ERROR_EMAIL");
    if (!$emailTo) {
        return;
    }
    $error = error_get_last();
    if (dontSend($error)) {
        return;
    }
    if ($error !== null) {
        $service = new EmailService();
        $service->sendEmail(
            $emailTo,
            "Error: Port Activity App (shutdownHandler)",
            print_r($error, true)
        );
    }
}
