<?php

namespace test;

use c00\common\CovleDate;
use c00\log\channel\sql\Database;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogQuery;
use PHPUnit\Framework\TestCase;

class LogToSqlTest extends TestCase
{
	const TAG = 'test';

    /** @var  Database */
    private $db;

    /** @var TestHelper */
    private $th;

    public function setUp(){
        $this->th = new TestHelper();
	    $this->th->setupSql();

	    $this->db = $this->th->db;
    }

    public function testInit(){
        Log::debug(self::TAG, "first message");
        Log::info(self::TAG, "info message");
        Log::error(self::TAG, "error message");

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

        Log::info(self::TAG, "A fourth message");
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
        $this->th->addTestLogs();

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




}