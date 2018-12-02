<?php

namespace test;

use c00\common\CovleDate;
use c00\log\channel\sql\Database;
use c00\log\channel\sql\LogChannelSQL;
use c00\log\channel\sql\SqlAbstractChannelSettings;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogQuery;
use c00\log\LogSettings;
use PHPUnit\Framework\TestCase;

class LogToSqlTest extends TestCase
{
    /** @var \PDO */
    private $pdo;

    /** @var  Database */
    private $db;

    public function setUp(){
        $database = "test_log";
        $username = "root";
        $password = "";
        $host = "127.0.0.1";

        //Setup logging
        $settings               = LogSettings::new(false)
            ->addSqlChannelSettings($host, $username, $password, $database, null, Log::EXTRA_DEBUG);
        $settings->defaultLevel = Log::INFO;

        /** @var SqlAbstractChannelSettings $sqlSettings */
        $sqlSettings = $settings->getChannelSettings(LogChannelSQL::class);

        //Setup database
        $this->pdo = new \PDO(
            "mysql:charset=utf8mb4;host={$sqlSettings->host};dbname={$sqlSettings->database}",
            $sqlSettings->username,
            $sqlSettings->password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false]
        );

        //empty logs
        $sql = "SET foreign_key_checks = 0; TRUNCATE TABLE `log_bag`; TRUNCATE TABLE `log_item`;";
        $this->pdo->exec($sql);

        //DB for getting info
        $this->db = new Database($sqlSettings);
        $this->db->setupTables();

        Log::init($settings);
    }

    public function testInit(){
        Log::debug("first message");
        Log::info("info message");
        Log::error("error message");

        Log::flush();

        //Get messages
        $logs = $this->db->getLastBag();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('first message', $logs->logItems[0]->message);
        $this->assertEquals('info message', $logs->logItems[1]->message);
        $this->assertEquals('error message', $logs->logItems[2]->message);
        $this->assertEquals(Log::DEBUG, $logs->logItems[0]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[1]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[2]->level);

        //flush again to see if it generates an error
        Log::flush();


        Log::info("A fourth message");
        Log::flush();
        $logs = $this->db->getLastBag();

        $this->assertEquals('first message', $logs->logItems[0]->message);
        $this->assertEquals('info message', $logs->logItems[1]->message);
        $this->assertEquals('error message', $logs->logItems[2]->message);
        $this->assertEquals('A fourth message', $logs->logItems[3]->message);
        $this->assertEquals(Log::DEBUG, $logs->logItems[0]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[1]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[2]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[3]->level);
    }

    public function testQueryLast() {
        $this->addTestLogs();

        $q = new LogQuery();
        //Only a since date, that's after stuff was added. Should return nothing.
        $q->since = CovleDate::now()->addSeconds(5);
        $bags = $this->db->queryBags($q);

        $this->assertEquals(0, count($bags));

        //Move the time to before they were made
        $q->since->addSeconds(-10);
        $bags = $this->db->queryBags($q);

        $this->assertEquals(1, count($bags));
        $this->assertEquals(15, count($bags[0]->logItems));

        //Get only errors
        $q->includeLevels = [Log::ERROR];

        $bags = $this->db->queryBags($q);
        $this->assertEquals(3, count($bags[0]->logItems));
        foreach ($bags[0]->logItems as $logItem) {
            $this->assertEquals(Log::ERROR, $logItem->level);
        }

        //Get errors and debugs
        $q->includeLevels = [Log::ERROR, Log::DEBUG];

        $bags = $this->db->queryBags($q);
        $this->assertEquals(6, count($bags[0]->logItems));
        foreach ($bags[0]->logItems as $logItem) {
            $this->assertTrue(in_array($logItem->level, $q->includeLevels));
        }

    }

    private function addTestLogs() {
        Log::init();
        Log::extraDebug("extra debug message1");
        Log::debug("debug message1");
        Log::info("info message1");
        Log::warning("warning message1");
        Log::error("error message1");
        Log::extraDebug("extra debug message2");
        Log::debug("debug message2");
        Log::info("info message2");
        Log::warning("warning message2");
        Log::error("error message2");
        Log::extraDebug("extra debug message3");
        Log::debug("debug message3");
        Log::info("info message3");
        Log::warning("warning message3");
        Log::error("error message3");

        Log::flush();
    }


}