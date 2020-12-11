<?php
namespace SMA\PAA;

use ReflectionMethod;
use Exception;
use SMA\PAA\SERVICE\AuthService;
use SMA\PAA\TOOL\PermissionTools;
use SMA\PAA\SERVICE\StateService;
use SMA\PAA\SERVICE\IStateService;

class App
{
    private $server;

    public function __construct(Server $server, IStateService $stateService = null)
    {
        $this->server = $server;

        if ($stateService === null) {
            $stateService = new StateService();
        }
        $stateService->rebuildInitialSharedData();
    }
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }
    private function resolveAliasesServices()
    {
        $queryTokens = explode("?", $this->server->requestUri());
        $path = $queryTokens[0];
        $requestMethod = $this->server->requestMethod();
        $methodPath = "$requestMethod:" . $path;
        $matches = [];
        foreach ($this->aliases as $aliasKey => $aliasService) {
            $aliasTokens = explode(":", $aliasKey);
            $aliasMethod = array_shift($aliasTokens);
            $aliasPath = "/" . trim(array_shift($aliasTokens), "/");
            $preg = ",^$aliasMethod:$aliasPath.*,";
            if (preg_match($preg, $methodPath)) {
                $matches[] = [
                    "request_method" => $requestMethod,
                    "request_path" => $path,
                    "alias" => $aliasKey,
                    "path" => $aliasPath,
                    "service" => $aliasService,
                    "is_exact" => $path == $aliasPath
                ];
            };
        }
        usort($matches, function ($a1, $a2) {
            if ($a1["is_exact"]) {
                return false;
            } else {
                return strlen($a1["alias"]) < strlen($a2["alias"]);
            }
        });
        return isset($matches[0]) ? $matches[0] : false;
    }
    public function run()
    {
        $matchingAlias = $this->resolveAliasesServices();
        if ($matchingAlias) {
            list($permission, $service, $method) = explode(":", $matchingAlias["service"]);
            if (!preg_match('/^[A-Z]/', $service)) {
                throw new Exception("Service should start with capital letter: "  . $service);
            }
            if (!preg_match('/^[A-Z][A-z]+$/', $service)) {
                throw new Exception("Invalid service: " . $service);
            }
            $permissionTools = new PermissionTools(new Session());
            if ($permissionTools->hasPermission($permission, $this->server->bodyParameters())) {
                $class = "SMA\\PAA\\SERVICE\\" . $service . "Service";
                preg_match_all(",:[A-z0-9]+,", $matchingAlias["alias"], $matches);
                $preg2 = $matchingAlias["path"] . str_repeat("/(.+)", sizeof($matches[0]));
                preg_match(",$preg2,", $this->server->requestUri(), $parameterMatches);
                array_shift($parameterMatches);
                $data = [];
                for ($i=0; $i < sizeof($parameterMatches); $i++) {
                    $data[trim($matches[0][$i], ":")] = $parameterMatches[$i];
                }
                if (in_array($this->server->requestMethod(), array("POST", "PUT", "DELETE"))) {
                    $data = array_merge($data, $this->server->bodyParameters());
                }
                if (in_array($this->server->requestMethod(), array("GET"))) {
                    $data = array_merge($data, $this->server->getQueryParameters());
                }
                foreach ($data as $k => $v) {
                    if (is_string($v)) {
                        $data[$k] = trim($v);
                    }
                }
                $reflection = new ReflectionMethod($class, $method);
                $parameters = [];
                foreach ($reflection->getParameters() as $parameter) {
                    if (!array_key_exists($parameter->name, $data) && !$parameter->isOptional()) {
                        throw new InvalidParameterException("Missing parameter: " . $parameter->name);
                    } elseif ($parameter->hasType()
                        && array_key_exists($parameter->name, $data)
                        && !$parameter->isOptional()
                        && $parameter->getType()->getName() == "int"
                        && !preg_match('/^[0-9]+$/', $data[$parameter->name])
                    ) {
                        throw new InvalidParameterException(
                            "Wrong parameter type: " . $parameter->name . " should be " . $parameter->getType()
                            . " but got '" . $data[$parameter->name] . "'"
                        );
                    } elseif ($parameter->hasType()
                        && array_key_exists($parameter->name, $data)
                        && !$parameter->isOptional()
                        && $parameter->getType()->getName() != "int"
                        && $parameter->getType()->getName() != gettype($data[$parameter->name])
                    ) {
                        throw new InvalidParameterException(
                            "Wrong parameter type: " . $parameter->name
                            . " should be " . $parameter->getType()
                            . " but got '" . print_r($data[$parameter->name], true) . "'"
                        );
                    } elseif (!array_key_exists($parameter->name, $data) && $parameter->isOptional()) {
                        $parameters[$parameter->name] = $parameter->getDefaultValue();
                    } else {
                        $parameters[$parameter->name] = $data[$parameter->name];
                    }
                }
                $object = new $class;
                return call_user_func_array([$object, $method], $parameters);
            }
        }
        AuthService::handleInvalidAccess();
    }
}
