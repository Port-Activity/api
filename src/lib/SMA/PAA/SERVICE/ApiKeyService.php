<?php
namespace SMA\PAA\SERVICE;

use SMA\PAA\ORM\ApiKeyRepository;
use SMA\PAA\ORM\ApiKeyModel;
use SMA\PAA\ORM\TimestampStateRepository;
use SMA\PAA\ORM\TimestampTimeTypeRepository;
use SMA\PAA\ORM\UserModel;
use SMA\PAA\ORM\UserRepository;
use SMA\PAA\SERVICE\UserService;
use SMA\PAA\Session;

class ApiKeyService implements IApiKeyService
{
    public function generateApiKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(25));
    }
    public function create($name)
    {
        $key = $this->generateApiKey();
        return $this->save($name, $key, true);
    }
    public function disable(int $id): bool
    {
        return $this->enableDisable($id, false);
    }
    public function enable(int $id): bool
    {
        return $this->enableDisable($id, true);
    }
    private function enableDisable(int $id, bool $is_active): bool
    {
        $repository = new ApiKeyRepository();
        $model = $repository->get($id);
        //note: false causes error when set to db
        $model->is_active = $is_active ? 1 : 0;
        $repository->save($model);
        return true;
    }
    private function save($name, $key, $isActive)
    {
        $service = new UserService();
        $userData = $service->addWithoutPassword(time() . "-" . uniqid() . "@api.key", "ApiKey", "ApiKey");

        $userRepository = new UserRepository();
        $userModel = $userRepository->get($userData["id"]);
        $userModel->status = UserModel::STATUS_API_USER;
        $userRepository->save($userModel);

        $repository = new ApiKeyRepository();
        $model = new ApiKeyModel();
        $model->set($userData["id"], $name, $key, $isActive);
        $repository->save($model);
        return $model->buildValues(["id", "name", "key"]);
    }
    private function apiKeyModel($key)
    {
        $repository = new ApiKeyRepository();
        return $repository->first(["key" => $key]);
    }
    private function stateToStateId($state)
    {
        $repository = new TimestampStateRepository();
        return $repository->mapToId($state);
    }
    private function timeTypeToTimeTypeId($timeType)
    {
        $repository = new TimestampTimeTypeRepository();
        return $repository->mapToId($timeType);
    }

    public function list($limit, $offset, $sort, $search = '')
    {
        $query = [];
        if ($search) {
            $query['name'] = ['ilike' => '%' . $search .'%'];
        }
        $repository = new ApiKeyRepository();
        return $repository->listPaginated($query, $offset, $limit, $sort);
    }
    private function user($id): ?UserModel
    {
        $userRepository = new UserRepository();
        return $userRepository->get($id);
    }
    public function userOrApiKeyName(int $id)
    {
        $user = $this->user($id);
        if ($user && $user->status == UserModel::STATUS_API_USER) {
            $repository = new ApiKeyRepository();
            $model = $repository->first(["bound_user_id" => $id]);
            return $model ? $model->name : "";
        }
        return $user ? $user->first_name . " " . $user->last_name . " (" . $user->email . ")" : "";
    }
    public function getApiKeyId(): ?int
    {
        $session = new Session();
        $apiKeyId = null;
        if ($session->apiKey() !== null) {
            $apiKeyModel = $this->apiKeyModel($session->apiKey());
            if ($apiKeyModel !== null) {
                $apiKeyId = $apiKeyModel->id;
            }
        }

        return $apiKeyId;
    }
}
