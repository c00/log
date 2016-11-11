<?php
namespace c00\log;
use c00\common\CovleDate;
use c00\common\Helper as H;
use \MongoDate;

/**
 * A single log item.
 *
 * @author Coo
 */
class LogItem {

    var $microtime, $caller, $trace, $message, $level, $object, $id;
    var $bag_id;

    /**
     * @var CovleDate
     */
    var $date;
    
    //view properties
    var $level_string, $unique_id;

    public function __construct($level, $message, $trace, $object = 0) {
        $this->level = $level;
        $this->message = $message;
        $this->trace = $this->GetTrace($trace);
        $this->id = uniqid("", true);

        if ($object) {
            //For safety reasons this is being encoded to JSON.
            $this->object = json_encode($object);
        }
        $this->date = new CovleDate();
        //$this->date = date("Y-m-d H:i:s");
        $this->microtime = microtime(true);

        if (is_array($trace) && isset($trace[0])) {
            $this->caller = $trace[0]['file'] . " line: " . $trace[0]['line'];
        }

        //Add view properties
        $this->level_string = Log::levelString($this->level);
        $this->unique_id = $this->id;
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

    static function fromMongo(array $array){
        $item = new LogItem($array['level'], $array['message'], $array['trace']);
        H::copyArrayPropertiesToObject($array, $item);

        //old items won't be in mongoDate format.
        if (is_string($array['date']) == MongoDate::class){
            //Turn into CovleDate from old version.
            $item->date = CovleDate::fromString($array['date']);
        } else if (is_object($array['date'])) {
            $item->date = CovleDate::fromMongoDate($array['date']);
        } else {
            //Error
            $item->date = CovleDate::fromMilliseconds(0);
        }

        return $item;
    }

    static function fromSql($stringData){
        $array = json_decode($stringData, true);

        $item = new LogItem($array['level'], $array['message'], $array['trace']);
        H::copyArrayPropertiesToObject($array, $item);

        $item->date = CovleDate::fromSeconds($array['date']);

    return $item;
}

    function toMongo(){
        $array = H::objectToArray($this);
        $array['date'] = $this->date->toMongoDate();

        return $array;
    }

    function toSql(){
        $array = H::objectToArray($this);
        $array['date'] = $this->date->toSeconds();

        return json_encode($array);
    }

    function toShowable(){
        $array = H::objectToArray($this);
        $array['date'] = $this->date->toMiliseconds();

        return $array;
    }

}
