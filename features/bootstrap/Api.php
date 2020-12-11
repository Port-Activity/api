<?php

final class Api
{
    public function login($email, $password)
    {
        return $this->post(false, "login", ["email" => $email, "password" => $password]);
    }
    public function loginAndSendBearerToken($email, $password, $sessionId)
    {
        return $this->post($sessionId, "login", ["email" => $email, "password" => $password]);
    }
    private function call($method, $sessionId, $apiKey, $path, $values)
    {
        $url = (getenv("INTEGRATION_HOST_ROOT") ?: "http://api/") . $path;
        $data = json_encode($values);
        $headers = [
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        if ($sessionId) {
            $headers[] = "Authorization: Bearer $sessionId";
        }
        if ($apiKey) {
            $headers[] = "Authorization: ApiKey $apiKey";
        }
        $ch = curl_init($url);
        #curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $method === "post" && curl_setopt($ch, CURLOPT_POST, 1);
        $method === "put" && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $method === "get" && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $method === "delete" && curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        return json_decode($body, true);
    }
    public function post($sessionId, $path, $values)
    {
        return $this->call("post", $sessionId, "", $path, $values);
    }
    public function postWithApiKey($apiKey, $path, $values)
    {
        return $this->call("post", "", $apiKey, $path, $values);
    }
    public function put($sessionId, $path, $values)
    {
        return $this->call("put", $sessionId, "", $path, $values);
    }
    public function get($sessionId, $path)
    {
        return $this->call("get", $sessionId, "", $path, []);
    }
    public function delete($sessionId, $path, $values = [])
    {
        return $this->call("delete", $sessionId, "", $path, $values);
    }
}
