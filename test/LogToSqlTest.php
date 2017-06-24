<?php
/**
 * Created by PhpStorm.
 * User: Co
 * Date: 18/06/2016
 * Time: 01:12
 */

namespace test;

use c00\log\channel\LogChannelOnScreen;
use c00\log\channel\LogChannelStdError;
use c00\log\channel\sql\Database;
use c00\log\channel\sql\LogChannelSQL;
use c00\log\channel\sql\SqlChannelSettings;
use c00\log\ChannelSettings;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogSettings;

class LogToSqlTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PDO */
    private $pdo;

    /** @var  Database */
    private $db;

    public function setUp(){
        $database = "test_log";
        $username = "root";
        $password = "";
        $host = "localhost";

        //Setup logging
        $settings = LogSettings::newInstance(false)
            ->addSqlChannelSettings($host, $username, $password, $database);
        $settings->level = Log::INFO;

        /** @var SqlChannelSettings $sqlSettings */
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
        Log::init();
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



}