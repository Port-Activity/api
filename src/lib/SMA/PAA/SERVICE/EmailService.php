<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\TOOL\EmailTools;

class EmailService
{
    public function sendEmail(string $emailsTo, string $subject, string $text, string $html = null)
    {
        if (!getenv("SENDGRID_API_KEY")) {
            error_log("WARNING: Missing SENDGRID_API_KEY from env!");
            return false;
        }
        $tools = new EmailTools();
        $emailsToArray = $tools->parseAndValidate($emailsTo);
        if (!$emailsToArray) {
            throw new \Exception("Invalid email(s): " . $emailsTo);
        }
        $sendgrid = new \SendGrid(getenv("SENDGRID_API_KEY"));
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(getenv("FROM_EMAIL"));
        $email->setSubject($subject);
        foreach ($emailsToArray as $oneEmailTo) {
            $email->addTo($oneEmailTo);
        }
        $email->addContent("text/plain", $text);
        if ($html !== null) {
            $email->addContent("text/html", $html);
        }
        try {
            $sendgrid->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Email sending exception: '. $e->getMessage());
        }
        return false;
    }
}
