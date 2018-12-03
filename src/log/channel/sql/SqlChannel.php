<?php
namespace c00\log\channel\sql;

use c00\log\channel\iLogChannel;
use c00\log\LogBag;
use c00\log\LogException;
use c00\log\LogItem;

/**
 * Log Channel to dump items in database
 *
 * @author Coo
 */
class SqlChannel implements iLogChannel {

    /** @var  SqlSettings */
    public $settings;

    /** @var  Database */
    private $db;
    
    /** @var LogBag */
    private $logBag;

	/**
	 * LogChannelSQL constructor.
	 *
	 * @param SqlSettings $settings
	 *
	 * @throws \Exception
	 */
    public function __construct($settings) {
        if (!$settings instanceof SqlSettings) throw LogException::new("No settings for SQL logging");

        $this->db = new Database($settings);
        $this->settings = $settings;
    }

    public function setUserId($userId){
        $this->logBag->userId = $userId;
    }

    public function setBag(LogBag $logBag){
        $this->logBag = $logBag;
    }

    public function flush() {
        if (count($this->logBag->logItems) === 0) {
            return true;
        }

        //Stop if no connection
        if (!is_object($this->db)) {
            return false;
        }

        //Write everything to database
        if (!$this->db->saveBag($this->logBag)){
            return false;
        }

        $this->logBag->logItems = [];
        return true;
    }


    public function log(LogItem $item) {
        if ($this->settings->level < $item->level) {
            return false;
        }

        $this->logBag->logItems[] = $item;
        return true;
    }

}
