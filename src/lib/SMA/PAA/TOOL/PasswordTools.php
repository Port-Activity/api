<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\AuthenticationException;
use SMA\PAA\TOOL\PasswordRules;

class PasswordTools
{
    private $passwordRules;

    public function __construct()
    {
        $this->passwordRules = new PasswordRules();
    }

    public function checkPasswordIsOk(string $username, string $password, string $role)
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === $password) {
            throw new AuthenticationException("Password can't be same as username");
        }

        if (strlen(trim($password)) < $this->passwordRules->getPasswordMinLength($role)) {
            throw new AuthenticationException(
                "Password must be at least " .
                $this->passwordRules->getPasswordMinLength($role) .
                " characters in length"
            );
        }
        $tokens = str_split($password);
        sort($tokens);
        $tokens = array_unique($tokens);

        if (sizeof($tokens) < $this->passwordRules->getPasswordMinUniqueChars($role)) {
            throw new AuthenticationException("Weak password. Not enough unique characters in password.");
        }

        return true;
    }
}
