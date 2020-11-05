<?php
namespace SMA\PAA;

use SMA\PAA\SERVICE\UserService;
use SMA\PAA\ORM\ApiKeyRepository;
use SMA\PAA\ORM\ApiKeyModel;
use SMA\PAA\ORM\UserModel;
use SMA\PAA\ORM\UserRepository;

const KEY = __CLASS__ . "_user";
class Session
{
    private $data;
    public function __construct($data = [])
    {
        $this->data = $data ? $data : (isset($_SESSION) ? $_SESSION : []);
    }
    public function updateUserTimeZone(string $timeZone)
    {
        $userService = new UserService();
        $userService->updateUserLastKnownTimeZone($this->userId(), $timeZone);
    }
    public function start(Server $server)
    {
        if ($server->authorization()) {
            list($prefix, $key) = explode(" ", $server->authorization());
            if ($prefix === "Bearer" && $key) {
                $this->startWithSessionId($key);
                if ($server->clientTimeZone()) {
                    $this->updateUserTimeZone($server->clientTimeZone());
                }
            } elseif ($prefix === "ApiKey" && $key) {
                if ($model = $this->isValidApiKey($key)) {
                    $this->startWithApiKey($model);
                }
            }
        }
        !$this->started() && $this->startNewSession();
    }
    private function isValidApiKey($apiKey)
    {
        $repository = new ApiKeyRepository();
        $model = $repository->first(["key" => $apiKey]);
        return $model && $model->is_active === true ? $model : false;
    }
    private function started()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
    public function startWithUser(UserModel $user)
    {
        $this->startNewSession();
        $_SESSION["user_id"] = $user->id;
    }
    private function startWithSessionId($id)
    {
        session_id($id);
        $this->startNewSession();
        if ($id && !$this->userId()) {
            throw new SessionException('Session expired');
        }
        $userService = new UserService();
        $userService->updateUserLastSessionTime($this->userId());
    }
    private function startWithApiKey(ApiKeyModel $model)
    {
        $repository = new UserRepository();
        $userModel = $repository->get($model->bound_user_id);
        session_id("API-KEY-" . $model->key);

        $this->startNewSession();
        $_SESSION["api_key"] = $model->key;
        $_SESSION["user_id"] = $userModel->id;
        $this->readApiRole();
    }
    private function startNewSession()
    {
        session_start();
        $_SESSION["expires"] = time() + ini_get('session.gc_maxlifetime') - 10;

        $this->data = $_SESSION;
    }
    private function get($key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }
    public function userId()
    {
        return intval($this->get("user_id"));
    }
    public function user()
    {
        if (!isset($GLOBALS[KEY])) {
            $service = new UserService();
            $GLOBALS[KEY] = $service->get($this->userId());
        }
        return $GLOBALS[KEY];
    }
    public function apiKey()
    {
        return $this->get("api_key");
    }
    public function ttl()
    {
        return max(0, $this->get("expires") - time());
    }
    private function readApiRole()
    {
        if (getenv("API_ROLE")) {
            $api_role = getenv("API_ROLE");

            if ($api_role !== "master" && $api_role !== "slave") {
                throw new SessionException("Invalid API role " . $api_role);
            }

            $_SESSION["api_role"] = $api_role;
        }
    }
    public function getApiRole(): string
    {
        $apiRole = $this->get("api_role");
        if ($apiRole !== null) {
            return $apiRole;
        }

        return "slave";
    }
}
