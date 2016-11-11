<?php
namespace c00\log;
/**
 * Description of LogChannelOnScreen
 *
 * @author Coo
 */
class LogChannelOnScreen implements iLogChannel {
    public $level;
    public $logAudits;

    /** @var LogBag */
    public $logbag;
    
    public function __construct($settings) {
        $this->level = (isset($settings['level'])) ? $settings['level'] : Log::INFO;
        $this->logAudits = (isset($settings['logAudits'])) ? $settings['logAudits'] : true;

    }
    
    public function log(LogItem $item){
         if ($this->level < $item->level) {
            return 0;
        }
        
        $this->logbag->logItems[] = $item;
        return 1;
    }

    public function setBag(LogBag $logbag){
        $this->logbag = $logbag;
    }

    public function flush() {
        //Do nothing. Shouldn't empty the array.
        return true;
    }

}
