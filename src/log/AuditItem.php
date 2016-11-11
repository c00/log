<?php
namespace c00\log;
use \c00\common\Helper as H;
/**
 * A single Audit Item
 */
class AuditItem
{
    const SUCCESS = 10;
    const FAILED = 20;

    var $id, $type, $action, $userId, $message,  $object, $microtime;

    function __construct($type, $action, $userId, $message = null, $object = null){
        $this->microtime = microtime(true);
        $this->type = $type;
        $this->action = $action;
        $this->userId = $userId;

        if (isset($message)) $this->message = $message;
        if (isset($object)) $this->object = $object;

    }
    
    

    static function fromMongo($array){
        if (!is_array($array))return false;

        $i = new AuditItem('x', 'y', 'z');
        H::copyArrayPropertiesToObject($array, $i);

        return $i;
    }
}