<?php
namespace SMA\PAA;

use ReflectionClass;
use ReflectionType;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/lib/autoload.php";
require_once (__DIR__ . "/src/lib/routes.php");

echo "# API\n\n";

echo file_get_contents(__DIR__ . "/api_part_general.md");

$routes = routes();

foreach ($routes as $route => $service) {
    $tokens = explode(":", $route);
    $requestMethod = array_shift($tokens);
    $path = implode(":", $tokens);
    echo "## " . $requestMethod . ":" . $path . "\n\n";
    $tokens2 = explode(":", $service);
    $permission = $tokens2[0];
    $class = $tokens2[1] . "Service";
    $method = $tokens2[2];
    
    $reflection = new ReflectionClass("SMA\\PAA\\SERVICE\\$class");
    $methodReflection = $reflection->getMethod($method);
    $parameters = $methodReflection->getParameters();
    $doc = $methodReflection->getDocComment();
    
    if ($doc) {
        // TODO: parse doc once docs
        // echo "$doc\n\n";
    } else {
        // echo "No description yet.\n\n";
    }

    echo "Required permission: $permission\n\n";

    if ($parameters) {
        echo "Parameters:\n\n";
        $parameterList = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType() ? $parameter->getType()->getName() : "mixed";
            $parameterList[$parameter->getName()] = $type;
            echo "- " . $parameter->getName() . " **" . $type . "**";
            echo ($parameter->getClass()->name ? $parameter->getClass()->name  . " " : "");
            echo ($parameter->isOptional() ? " (optional)" : "");
            echo "\n\n";    
        }

    } else {
        echo "No parameters\n\n";
    }

    $get = "";
    $jsonData = [];
    $separator = "?";
    foreach ($parameterList as $name => $type) {
        if (!mb_stristr($path, ":$name")) {
            $get = $get . $separator . $name . "=<" . $name . ">";
            $separator = "&";
            $jsonData[$name] = $type === "array" ? [] : 
                ($type === "int" ? 123 : "<$name>")
            ;
        }
    }
    echo "```bash\n";
    echo "curl http://localhost:8000" . $path . ($requestMethod === "GET" ? $get : "") . " \\\n";
    echo "-X '" . $requestMethod . "'\\\n";
    echo "-H 'Content-Type: application/json;charset=utf-8' \\\n";
    echo "-H 'Accept: application/json, text/plain, */*' \\\n";
    echo "-H 'Authorization: Bearer yourbearer'\n";
    if ($parameters && $requestMethod !== "GET") {
        echo "-d '" . json_encode($jsonData) .  "'\n\n";
    }
    echo "```\n\n";
}

