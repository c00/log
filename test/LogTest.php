<?php
/**
 * Created by PhpStorm.
 * User: Co
 * Date: 18/06/2016
 * Time: 01:12
 */

namespace test;

use c00\log\Log;
use c00\log\LogBag;

class LogTest extends \PHPUnit_Framework_TestCase
{

    public function testInit(){
        Log::init();
        Log::debug("first message");
        Log::info("info message");
        Log::error("error message");

        $logs = Log::getLogForView();
        $logString = $logs->toString();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('first message', $logs->logItems[0]->message);
        $this->assertEquals('info message', $logs->logItems[1]->message);
        $this->assertEquals('error message', $logs->logItems[2]->message);
        $this->assertEquals(Log::DEBUG, $logs->logItems[0]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[1]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[2]->level);
    }

    public function testLevels(){
        Log::init();
        //Don't log debug and extra debug.
        Log::setLogLevel(Log::INFO);
        Log::extraDebug("extra debug message");
        Log::debug("debug message");
        Log::info("info message");
        Log::error("error message");

        $logs = Log::getLogForView();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('info message', $logs->logItems[0]->message);
        $this->assertEquals('error message', $logs->logItems[1]->message);
        $this->assertEquals(Log::INFO, $logs->logItems[0]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[1]->level);
        $this->assertEquals(2, count($logs->logItems));


        //Enable debug logging now
        Log::setLogLevel(Log::DEBUG);
        Log::debug("Debug message");
        Log::extraDebug("Extra debug message");

        $logs = Log::getLogForView();

        $this->assertEquals('info message', $logs->logItems[0]->message);
        $this->assertEquals('error message', $logs->logItems[1]->message);
        $this->assertEquals('Debug message', $logs->logItems[2]->message);

        $this->assertEquals(Log::INFO, $logs->logItems[0]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[1]->level);
        $this->assertEquals(Log::DEBUG, $logs->logItems[2]->level);
        $this->assertEquals(3, count($logs->logItems));
    }


}