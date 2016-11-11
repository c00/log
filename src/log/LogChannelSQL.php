<?php
namespace c00\log;
/**
 * Log Channel to dump items in database
 *
 * @author Coo
 */
//todo needs serious refactoring
class LogChannelSQL extends LogDb implements iLogChannel {

    var $level;
    
    /**
     * @var LogBag
     */
    var $logbag;

    public function __construct($settings) {
        $this->level = (isset($settings['level'])) ? $settings['level'] : Log::INFO;
        $db_server = (isset($settings['db_server'])) ? $settings['db_server'] : "localhost";
        $db_user = (isset($settings['db_user'])) ? $settings['db_user'] : "no_user";
        $db_password = (isset($settings['db_password'])) ? $settings['db_password'] : "password";
        $db_name = (isset($settings['db_name'])) ? $settings['db_name'] : "no_db_name";
        
        parent::__construct($db_server, $db_name, $db_user, $db_password);
    }

    public function setUserId($userId){
        $this->logbag->user_id = $userId;
    }

    public function setBag(LogBag $logbag){
        $this->logbag = $logbag;
    }

    public function flush() {
        if (count($this->logbag->logItems) === 0) {
            return true;
        }

        //Stop if no connection
        if (!is_object($this->db)) {
            return false;
        }

        //Write everything to database
        if (!$this->saveBag($this->logbag)){
            return false;
        }
        $this->logbag->logItems = [];
        return true;
    }


    public function log(LogItem $item) {
        if ($this->level < $item->level) {
            return 0;
        }

        $this->logbag->logItems[] = $item;
        return 1;
    }

}
