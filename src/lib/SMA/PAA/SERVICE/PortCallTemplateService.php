<?php
namespace SMA\PAA\SERVICE;

class PortCallTemplateService
{
    private function returnIfExists($key, $array)
    {
        return key_exists($key, $array) ? $array[$key] : null;
    }
    private function buildPayload(string $payloadTemplate)
    {
        $payload = [];
        if ($payloadTemplate) {
            $tokens = explode(",", $payloadTemplate);
            foreach ($tokens as $token) {
                list($k, $v) = explode("=", $token);
                $payload[$k] = $v;
            }
        }
        return $payload;
    }
    public function get(string $namespace)
    {
        $template = getenv("PORT_CALL_TEMPLATE_" . strtoupper($namespace));
        if (!$template) {
            throw new \Exception("No port call template for namespace " . $namespace);
        }
        return $this->build($template);
    }
    public function build(string $template)
    {
        $tokens = explode(";", trim(str_replace("\n", ";", $template), ";"));
        return array_map(function ($token) {
            $tokens2 = explode(":", $token);

            return [
                "group" => $this->returnIfExists(0, $tokens2)
                ,"time_type" => $this->returnIfExists(1, $tokens2)
                ,"state" => $this->returnIfExists(2, $tokens2)
                ,"payload" => $this->buildPayload($this->returnIfExists(3, $tokens2) ?: "")
            ];
        }, $tokens);
    }
}
