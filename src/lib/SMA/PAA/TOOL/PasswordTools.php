<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\AuthenticationException;

class PasswordTools
{
    public function checkPasswordIsOk(string $username, string $password)
    {
        $username = trim($username);
        $password = trim($password);

        if ($username === $password) {
            throw new AuthenticationException("Password can't be same as username");
        }

        if (strlen(trim($password)) < 12) {
            throw new AuthenticationException("Password must be at least 12 characters in length");
        }
        $tokens = str_split($password);
        sort($tokens);
        $tokens = array_unique($tokens);

        if (sizeof($tokens) < 3) {
            throw new AuthenticationException("Weak password. Not enough unique characters in password.");
        }

        return true;
    }
}
