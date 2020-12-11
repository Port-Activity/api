<?php
namespace SMA\PAA\TOOL;

use SMA\PAA\TOOL\PasswordRulesContainer;

class PasswordRules
{
    private $passwordRules;

    public function __construct()
    {
        $this->passwordRules = [];

        $this->passwordRules["default"] = new PasswordRulesContainer();
        $this->passwordRules["default"]->passwordMinLength = 12;
        $this->passwordRules["default"]->passwordMinUniqueChars = 3;
        $this->passwordRules["default"]->suspendFailures = 3;
        $this->passwordRules["default"]->suspendMinutes = 15;
        $this->passwordRules["default"]->lockFailures = 15;

        $passwordRulesJson = getenv("PASSWORD_RULES");
        $passwordRulesArray = [];
        if (!empty($passwordRulesJson)) {
            $passwordRulesArray = json_decode($passwordRulesJson, true);
        }

        if (!empty($passwordRulesArray)) {
            foreach ($passwordRulesArray as $role => $rules) {
                if (!isset($this->passwordRules[$role])) {
                    $this->passwordRules[$role] = new PasswordRulesContainer();
                }

                foreach ($rules as $rule => $value) {
                    if ($rule === "password_min_length") {
                        $this->passwordRules[$role]->passwordMinLength = $value;
                    } elseif ($rule === "password_min_unique_chars") {
                        $this->passwordRules[$role]->passwordMinUniqueChars = $value;
                    } elseif ($rule === "suspend_failures") {
                        $this->passwordRules[$role]->suspendFailures = $value;
                    } elseif ($rule === "suspend_minutes") {
                        $this->passwordRules[$role]->suspendMinutes = $value;
                    } elseif ($rule === "lock_failures") {
                        $this->passwordRules[$role]->lockFailures = $value;
                    }
                }
            }
        }
    }

    private function get(string $role, string $value)
    {
        $res = $this->passwordRules["default"]->$value;

        if (isset($this->passwordRules[$role])) {
            if ($this->passwordRules[$role]->$value !== null) {
                $res = $this->passwordRules[$role]->$value;
            }
        }

        return $res;
    }

    public function getPasswordMinLength(string $role)
    {
        return $this->get($role, "passwordMinLength");
    }

    public function getPasswordMinUniqueChars(string $role)
    {
        return $this->get($role, "passwordMinUniqueChars");
    }

    public function getSuspendFailures(string $role)
    {
        return $this->get($role, "suspendFailures");
    }

    public function getSuspendMinutes(string $role)
    {
        return $this->get($role, "suspendMinutes");
    }

    public function getLockFailures(string $role)
    {
        return $this->get($role, "lockFailures");
    }
}
