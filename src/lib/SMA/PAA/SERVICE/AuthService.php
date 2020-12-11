<?php

namespace SMA\PAA\SERVICE;

use SMA\PAA\AuthenticationException;
use SMA\PAA\InvalidParameterException;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\Session;
use SMA\PAA\SERVICE\EmailService;
use SMA\PAA\SERVICE\JwtService;
use SMA\PAA\SERVICE\UserService;
use SMA\PAA\TOOL\EmailTools;
use SMA\PAA\ORM\SettingRepository;
use SMA\PAA\ORM\RolePermissionRepository;
use SMA\PAA\TOOL\PasswordTools;
use SMA\PAA\TOOL\PasswordRules;

class AuthService
{
    public function login(string $email, string $password)
    {
        $repository = new UserRepository();

        $user = $repository->getWithEmail($email);
        if ($user) {
            $userService = new UserService();

            if ($user->getLocked()) {
                return ["message" => "Too many failed login attempts. Account locked."];
            }

            $passwordRules = new PasswordRules();
            $suspendMinutes = $passwordRules->getSuspendMinutes($user->getRole());
            if ($userService->isUserSuspended($user->id)) {
                return [
                    "message" =>
                    "Too many failed login attempts. Account suspended for " . $suspendMinutes . " minute(s)."
                ];
            }

            if ($this->verify($password, $user->password_hash)) {
                $userService->updateUserSuccessfulLogin($user->id);

                session_destroy();
                $session = new Session();
                $session->startWithUser($user);
                return array_merge([
                    "session_id" => session_id()
                ], $this->session());
            } else {
                $userService->updateUserFailedLogin($user->id);

                $failedUser = $repository->get($user->id);
                if ($failedUser !== null) {
                    if ($failedUser->getLocked()) {
                        return ["message" => "Too many failed login attempts. Account locked."];
                    } elseif ($userService->isUserSuspended($failedUser->id)) {
                        return [
                            "message" =>
                            "Too many failed login attempts. Account suspended for " . $suspendMinutes . " minute(s)."
                        ];
                    }
                }
            }
        }
        self::handleInvalidAccess();
    }
    public function logout()
    {
        session_destroy();
        return true;
    }
    public function hash(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    public function verify(string $password, string $password_hash)
    {
        return password_verify($password, $password_hash);
    }
    public function randomPassword()
    {
        return bin2hex(openssl_random_pseudo_bytes(15));
    }
    /**
     * @deprecated
     */
    public function info()
    {
        $session = new Session();
        return ["user" => $session->user()];
    }
    public function session()
    {
        $session = new Session();
        $user = $session->user();

        $settingRepository = new SettingRepository();

        $all_modules = array(
            "activity_module",
            "logistics_module",
            "queue_module",
            "map_module",
            "codeless_registration_module");
        foreach ($all_modules as $module) {
            $settingModel = $settingRepository->getSetting($module);
            $modules[$module] = ($settingModel === null) ? "disabled" : $settingModel->value;
        }

        $rolePermissionRepository = new RolePermissionRepository();
        $role = $user->getRole();
        $permissions = $rolePermissionRepository->getRolePermissions($role);

        $userService = new UserService();
        $userService->updateUserLastLogin($user->id);

        $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
        $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));
        $jwtService = new JwtService($privateKey, $publicKey);
        $jwtExpiresIn = 24 * 60 * 60;
        $jwt = $jwtService->encode(["user_id" => $user->id], $jwtExpiresIn);
        $rtaPointCoordinates = getenv("RTA_POINT_COORDINATES");
        if ($rtaPointCoordinates === false) {
            $rtaPointCoordinates = "";
        }
        $mapDefaultCoordinates = getenv("MAP_DEFAULT_COORDINATES");
        if ($mapDefaultCoordinates === false) {
            $mapDefaultCoordinates = "";
        }
        $mapDefaultZoom = getenv("MAP_DEFAULT_ZOOM");
        if ($mapDefaultZoom === false) {
            $mapDefaultZoom = "";
        }

        $time = time();
        return [
            "user" => [
                "id" => $user->id,
                "email" => $user->email,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "role" => $role,
                "permissions" => $permissions
            ],
            "modules" => $modules,
            "session" => [
                "time" => $time,
                "ttl" => $session->ttl(),
                "expires_ts" => $time + $session->ttl()
            ],
            "jwt" => $jwt,
            "rta_point_coordinates" => $rtaPointCoordinates,
            "map_default_coordinates" => $mapDefaultCoordinates,
            "map_default_zoom" => $mapDefaultZoom
        ];
    }
    public function requestPasswordReset(string $email, string $port)
    {
        if (!$email) {
            throw new InvalidParameterException("Email can't be empty");
        }
        if (!$port) {
            throw new InvalidParameterException("Port can't be empty");
        }
        $repository = new UserRepository();
        $user = $repository->getWithEmail($email);
        if ($user) {
            // Send reset mail if user has email
            $tools = new EmailTools();
            $emailsToArray = $tools->parseAndValidate($email);
            if (!$emailsToArray) {
                throw new InvalidParameterException("Not valid email address: " . $email);
            }

            $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
            $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));

            $formUrl = getenv("BASE_URL") . "/reset-password";
            $jwtService = new JwtService($privateKey, $publicKey);
            // Generate token
            $expiresIn = 24 * 60 * 60; // TODO: validity length, currently one day
            $token = $jwtService->encode(["email" => $email, "port" => $port], $expiresIn);
            $link = $formUrl . "?username=" . urlencode($email) . "&token=" . $token . "&port=" . urlencode($port);
            $expiresDate = date(\DateTime::ATOM, time() + $expiresIn);
            $service = new EmailService();
            $subject = "Port Activity App - reset password";
            $text =
                "You are receiving this e-mail because you made a request to reset your password."
                . "\nIn order to reset your password, visit the link below."
                . "\n\n " . $link
                . "\n\nThis link expires in " . $expiresDate . "."
                . "\n\nYou can use this link as long as it is valid.";

            try {
                $service->sendEmail($email, $subject, $text);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return ["message" => "Password reset requested"];
    }
    public function resetPassword(string $password, string $token)
    {
        if (!$password) {
            throw new InvalidParameterException("Password can't be empty");
        }
        if (!$token) {
            throw new InvalidParameterException("Token can't be empty");
        }

        $privateKey = json_decode(getenv("PRIVATE_KEY_JSON"));
        $publicKey = json_decode(getenv("PUBLIC_KEY_JSON"));

        $service = new JwtService($privateKey, $publicKey);
        try {
            $data = $service->decodeAndVerifyValidity($token);
        } catch (\Exception $e) {
            return ["message" => "Invalid token, you must request new one."];
        }

        if ($data && $data['email']) {
            // Token not expired, set password
            $userRepository = new UserRepository();
            $user = $userRepository->getWithEmail($data['email']);

            if ($user) {
                $tools = new PasswordTools();
                $tools->checkPasswordIsOk($data['email'], $password, $user->getRole());

                $userService = new UserService();

                $res = $userService->updateUser(
                    $user->id,
                    $user->email,
                    $user->first_name,
                    $user->last_name,
                    $password,
                    "",
                    false,
                    true
                );

                $userService->updateUserSuccessfulLogin($user->id);

                return $res;
            }
        }
        return ["message" => "Password reset failed, please try again"];
    }
    public function changePassword($email, $password)
    {
        $session = new Session();
        $sessionUser = $session->user();
        $userService = new UserRepository();
        $user = $userService->getWithEmail($email);
        if (!$user) {
            throw new InvalidParameterException("Can't find user with email: " . $email);
        }
        if ($user->getRole() === "admin") {
            if ($user->id === $sessionUser->id) {
                // ok to change
            } else {
                throw new InvalidParameterException("Admin can't change other admin password");
            }
        }

        $tools = new PasswordTools();
        $tools->checkPasswordIsOk($user->email, $password, $user->getRole());

        $service = new UserService();
        $service->updateUser(
            $user->id,
            $user->email,
            $user->first_name,
            $user->last_name,
            $password
        );
        return true;
    }
    public static function handleInvalidAccess()
    {
        usleep(rand(0, 2000) * 1000);
        throw new AuthenticationException("Invalid access");
    }
}
