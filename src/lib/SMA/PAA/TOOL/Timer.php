<?php
namespace SMA\PAA\TOOL;

class Timer
{
    public static function get(): Timer
    {
        if (!isset($GLOBALS[__CLASS__])) {
            $GLOBALS[__CLASS__] = new self();
        }
        return $GLOBALS[__CLASS__];
    }
    public function start()
    {
        $this->mark("start");
    }
    public function mark($message)
    {
        $this->markers[]  = [
            "message" => $message,
            "ts" => $this->start = microtime(true)
        ];
    }
    public function results()
    {
        $previous = 0;
        $first = 0;
        for ($i=0; $i < sizeof($this->markers); $i++) {
            $marker = $this->markers[$i];
            if (!$first) {
                $first = $marker["ts"];
                $previous = $marker["ts"];
            }
            $this->markers[$i]["total"] = $marker["ts"] - $first;
            $this->markers[$i]["delta"] = $marker["ts"] - $previous;
            $previous = $marker["ts"];
        }
        return $this->markers;
    }
}
