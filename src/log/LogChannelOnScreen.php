<?php
namespace c00\log;
/**
 * Description of LogChannelOnScreen
 *
 * @author Coo
 */
class LogChannelOnScreen implements iLogChannel {
    var $level, $logAudits;

    /**
     * @var LogBag
     */
    var $logbag;
    
    public function __construct($settings) {
        $this->level = (isset($settings['level'])) ? $settings['level'] : Log::INFO;
        $this->logAudits = (isset($settings['logAudits'])) ? $settings['logAudits'] : true;

    }

    public function setUserId($userId){
        $this->logbag->user_id = $userId;
    }
    
    public function log(LogItem $item){
         if ($this->level < $item->level) {
            return 0;
        }
        
        $this->logbag->log_items[] = $item;
        return 1;
    }

    public function audit(AuditItem $item){
        if (!$this->logAudits) return true;

        $trace = $trace = debug_backtrace();
        $logItem = new LogItem(Log::INFO, "Audit: $item->action, User: $item->userId ,message: $item->message", $trace, $item->object);

        $this->logbag->log_items[] = $logItem;
        return true;
    }

    public function setBag(LogBag $logbag){
        $this->logbag = $logbag;
    }

    public function flush() {
        //Do nothing. Shouldn't empty the array.
        return true;
    }

}
