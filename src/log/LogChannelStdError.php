<?php
namespace c00\log;
/**
 * Logs to Apache error log.
 *
 * @author Coo
 */
class LogChannelStdError implements iLogChannel {

    var $level, $logFailedAudits, $logSuccesfulAudits;
    /**
     * @var LogBag
     */
    var $logbag;

    public function __construct($settings) {
        $this->level = (isset($settings['level'])) ? $settings['level'] : Log::INFO;
        $this->logFailedAudits = (isset($settings['logFailedAudits'])) ? $settings['logFailedAudits'] : false;
        $this->logSuccesfulAudits = (isset($settings['logSuccesfulAudits'])) ? $settings['logSuccesfulAudits'] : false;
    }

    public function setUserId($userId){
        $this->logbag->user_id = $userId;
    }

    public function setBag(LogBag $logbag){
        $this->logbag = $logbag;
    }

    public function log(LogItem $item) {
        if ($this->level < $item->level) {
            return 0;
        }

        $seperator = "; ";
        $message = $this->logbag->id . $seperator
            . $item->date->toString() . $seperator
            . $item->id . $seperator
            . Log::levelString($item->level) . $seperator
            . $item->message . $seperator
            . "In " . $item->caller;

        error_log($message);
        return 1;
    }

    public function flush() {
        //Do nothing.
        return true;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }


}
