<?php
namespace c00\log;
use \MongoClient;
use \MongoCollection;
use \c00\common\CovleDate;

class LogMongoDb
{
    var $dbName;

    /**
     * @var MongoClient
     */
    var $m;
    /**
     * @var MongoCollection
     */
    var $logCollection;

    /**
     * @var MongoCollection
     */
    var $auditCollection;

    function __construct(){
        //Check if we have a setting for logDb.
        if (defined("logDb")){
            $this->dbName = constant("logDb");
        } else {
            $this->dbName = "log";
        }

        try{
            $this->m = new \MongoClient();
            $this->logCollection = $this->m->{$this->dbName}->{LogChannelMongo::LOG_COLLECTION};
            $this->auditCollection = $this->m->{$this->dbName}->{LogChannelMongo::AUDIT_COLLECTION};

        }catch (\MongoConnectionException $ex){
            http_response_code(500);
            die("Database error.");
        }
    }

    function getAudits(CovleDate $since){
        $result = [];
        $cursor = $this->auditCollection->find(['timestamp' => ['$gt' => $since->toMongoDate()]]);

        foreach($cursor->sort(['timestamp' => -1]) as $doc){
            //todo consider combining the same requestIds
            $result[] = LogBag::fromMongo($doc);
        }

        return $result;

    }

    function getLogs(CovleDate $since, $page = 0){
        $itemsPerPage = 20;
        $result = [];
        $skip = $page * $itemsPerPage;
        $cursor = $this->logCollection->find(['timestamp' => ['$gt' => $since->toMongoDate()]]);

        foreach($cursor
                    ->sort(['timestamp' => -1])
                    ->skip($skip)
                    ->limit($itemsPerPage)
                as $doc){
            //todo consider combining the same requestIds
            $result[] = LogBag::fromMongo($doc);
        }

        return $result;

    }

}