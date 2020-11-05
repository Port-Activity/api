<?php
namespace SMA\PAA;

class Response
{
    private $code;
    public function __construct($code = false)
    {
        $this->code = $code;
    }
    public function code()
    {
        return $this->code ? $this->code : http_response_code();
    }
}
