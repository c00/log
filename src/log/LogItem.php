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
    public $id;
    public $caller;
    public $trace;
    public $message;
    public $level;
    public $object;
    public $bagId;

    /** @var CovleDate */
    public $date;

    protected $_dataTypes = [
        'date' => CovleDate::class
    ];

    public static function newItem($level, $message, $trace, $object = 0) : LogItem
    {
        $item = new LogItem();

        $item->id = bin2hex(random_bytes(12));
        $item->level = $level;
        $item->message = $message;
        $item->trace = $item->GetTrace($trace);

        if ($object) {
            //For safety reasons this is being encoded to JSON.
            $item->object = json_encode($object);
        }
        $item->date = new CovleDate();

        if (is_array($trace) && isset($trace[0])) {
            $item->caller = $trace[0]['file'] . " line: " . $trace[0]['line'];
        }

        return $item;
    }

    public function __construct() {

    }

    public function toString(){
        $string = $this->date->toString() . " " .
            Log::levelString($this->level) . ": " .
            $this->message . PHP_EOL . "\t" .
            $this->caller;

        return $string;
    }

    private function GetTrace($trace) {
        if (!$trace) {
            return [];
        }

        $result = [];
        //function, file, line
        foreach ($trace as $trace_line) {
            $function = (isset($trace_line['function'])) ? $trace_line['function'] : "no function";
            $file = (isset($trace_line['file'])) ? $trace_line['file'] : "no file";
            $line = (isset($trace_line['line'])) ? $trace_line['line'] : "no line";
            $result[] = ["function" => $function,
                "line" => $line,
                "file" => $file];
        }

        return $result;
    }

    public static function fromArray($array)
    {
        /** @var LogItem $item */
        $item = parent::fromArray($array);

        if ($item->object) $item->object = json_decode($array['object'], true);
        if ($item->trace) $item->trace = json_decode($array['trace'], true);

        return $item;
    }

    public function toShowable(){
        $array = H::objectToArray($this);
        $array['date'] = $this->date->toMiliseconds();

        return $array;
    }

}
