<?php
namespace c00\log\channel\onScreen;
use c00\log\channel\iLogChannel;
use c00\log\LogBag;
use c00\log\LogItem;

/**
 * Description of LogChannelOnScreen
 *
 * @author Coo
 */
class OnScreenChannel implements iLogChannel {
    public $level;

    /** @var LogBag */
    public $logBag;

	/**
	 * OnScreenChannel constructor.
	 *
	 * @param OnScreenSettings $settings
	 */
    public function __construct($settings) {
        $this->level = $settings->level;
    }

    public function log(LogItem $item){
         if ($this->level < $item->level) {
            return false;
        }
        
        $this->logBag->logItems[] = $item;
        return true;
    }

    public function setBag(LogBag $logBag){
        $this->logBag = $logBag;
    }

    public function flush() {
        //Do nothing. Shouldn't empty the array.
        return true;
    }

}
