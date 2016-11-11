<?php
namespace c00\log;
/**
 * Log Channel to dump items in Mongo Database
 *
 * @author Coo
 */
class LogChannelMongo implements iLogChannel {
    const LOG_COLLECTION = "main";
    const AUDIT_COLLECTION = "audit";

    var $dbName;
    /**
     * @var bool
     */
    var $logAudits, $immediatlyLogAudits;

    var $level;
    //var $log_array = array();
    /**
     * @var LogBag
     */
    var $logbag;

    /**
     * @var \MongoClient
     */
    var $m;
    /**
     * @var \MongoCollection
     */
    var $logCollection;

    /**
     * @var \MongoCollection
     */
    var $auditCollection;

    /**
     * @param array $settings Takes 'level' int, 'logAudits' bool and 'immediatlyLogAudits' bool as possible settings.
     */
    public function __construct($settings) {
        //Check if we have a setting for logDb.
        if (defined("logDb")){
            $this->dbName = constant("logDb");
        } else {
            $this->dbName = "log";
        }


        $this->level = (isset($settings['level'])) ? $settings['level'] : Log::INFO;
        $this->logAudits = (isset($settings['logAudits'])) ? $settings['logAudits'] : true;
        $this->immediatlyLogAudits = (isset($settings['immediatlyLogAudits'])) ? $settings['immediatlyLogAudits'] : true;

        try{
            $this->m = new \MongoClient();
            $this->logCollection = $this->m->{$this->dbName}->{$this::LOG_COLLECTION};
            $this->auditCollection = $this->m->{$this->dbName}->{$this::AUDIT_COLLECTION};

        }catch (\MongoConnectionException $ex){
            http_response_code(500);
            die("Database error.");
        }

    }

    public function setUserId($userId){
        $this->logbag->user_id = $userId;
    }

    public function setBag(LogBag $logbag){
        $this->logbag = $logbag;
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

        $this->logbag->audit_items[] = $item;

        if ($this->immediatlyLogAudits) {
            $this->flushAudits();
        }
        return true;
    }

    public function flush() {
        //Save to mongo
        if (count($this->logbag->log_items) === 0) {
            return true;
        }

        //Write logs to database
        if (!$this->saveBag($this->logbag)){
            return false;
        }

        //Write audits to database (empty if ImmediatlyLogAudits == true)
        if (!$this->flushAudits()){
            return false;
        }

        //Empty the bag.
        $this->logbag->log_items = [];
        $this->logbag->audit_items = [];

        return true;
    }

    private function flushAudits(){
        if (count($this->logbag->audit_items) === 0) return true;


        $array = $this->logbag->toMongo();
        unset($array['log_items']);

        $result = $this->auditCollection->insert($array);
        if ($result['ok'] != 1){
            return false;
        }
        return true;
    }

    private function saveBag(LogBag $bag){
        if (count($bag->log_items) === 0) return true;

        $array = $bag->toMongo();
        if (count($array) === 0){
            Log::error("Bag is empty?!");
            Log::debug("bag", $bag);
            return false;
        }

        $result = $this->logCollection->insert($array);
        if ($result['ok'] != 1){
            return false;
        }
        return true;
    }

}
