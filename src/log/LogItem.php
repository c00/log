<?php
namespace c00\log;
use c00\common\AbstractDatabaseObject;
use c00\common\CovleDate;
use c00\common\Helper as H;

/**
 * A single log item.
 *
 * @author Coo
 */
class LogItem extends AbstractDatabaseObject
{
    public $caller;
    public $trace;
    public $message;
    public $level;
    public $object;
    public $id;
    public $bagId;

    /** @var CovleDate */
    public $date;

    public function __construct($level, $message, $trace, $object = null) {
        $this->id = bin2hex(openssl_random_pseudo_bytes(8));
        $this->level = $level;
        $this->message = $message;
        $this->trace = $this->GetTrace($trace);

        if ($object) {
            //For safety reasons this is being encoded to JSON.
            $this->object = json_encode($object);
        }
        $this->date = new CovleDate();

        if (is_array($trace) && isset($trace[0])) {
            $this->caller = $trace[0]['file'] . " line: " . $trace[0]['line'];
        }
    }

    public function toString(){
        $string = $this->date->toString() . " " .
            Log::levelString($this->level) . ": " .
            $this->message . PHP_EOL . "\t" .
            $this->caller;

        if ($this->object){
            //Pretty print first
            $pretty = json_encode(json_decode($this->object), JSON_PRETTY_PRINT);
            $string .= PHP_EOL . $pretty;
        }

        return $string;
    }

    private function GetTrace($trace) {
        if (!$trace) {
            return [];
        }

        $result = [];
        //function, file, line
        foreach ($trace as $trace_line) {
            $function = (isset($trace_line['function'])) ? $trace_line['function'] : "no functon";
            $file = (isset($trace_line['file'])) ? $trace_line['file'] : "no file";
            $line = (isset($trace_line['line'])) ? $trace_line['line'] : "no line";
            $result[] = ["function" => $function,
                "line" => $line,
                "file" => $file];
        }

        return $result;
    }

    public function toShowable(){
        $array = H::objectToArray($this);
        $array['date'] = $this->date->toString();

        return $array;
    }

}
