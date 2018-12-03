<?php
namespace c00\log\channel\stdError;

use c00\log\channel\iLogChannel;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogItem;

/**
 * Logs to Apache error log.
 *
 * @author Coo
 */
class StdErrorChannel implements iLogChannel {

    public $level;

    /** @var LogBag */
    public $logBag;

	/**
	 * StdErrorChannel constructor.
	 *
	 * @param StdErrorSettings $settings
	 */
    public function __construct($settings) {
        $this->level = $settings->level;
    }

    public function setUserId($userId){
        $this->logBag->userId = $userId;
    }

    public function setBag(LogBag $logBag){
        $this->logBag = $logBag;
    }

    public function log(LogItem $item) {
        if ($this->level < $item->level) {
            return 0;
        }

        $separator = "; ";
        $message = $this->logBag->id . $separator
            . $item->date->toString() . $separator
            . $item->id . $separator
            . Log::levelString($item->level) . $separator
            . $item->message . $separator
            . "In " . $item->caller;

        error_log($message);
        return 1;
    }

    public function flush() {
        //Do nothing.
        return true;
    }

}
