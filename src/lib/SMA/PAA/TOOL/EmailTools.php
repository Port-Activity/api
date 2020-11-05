<?php
namespace SMA\PAA\TOOL;

class EmailTools
{
    public function isValid($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function parseAndValidate(string $emailOrEmails)
    {
        $emails = $this->emailsStringToArray($emailOrEmails);
        foreach ($emails as $email) {
            if (!$this->isValid($email)) {
                return false;
            }
        }
        return $emails;
    }
    public function emailsStringToArray(string $emailOrEmails)
    {
        return array_filter(array_map(function ($email) {
            return trim($email);
        }, explode(" ", $emailOrEmails)), function ($email) {
            return $email;
        });
    }
}
