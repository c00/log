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

    public function testObject(){

        $object = new \stdClass();
        $object->name = "Willem";
        $object->email = "willem@covle.com";

        Log::init();
        Log::debug("first message", $object);

        $logs = Log::getLogForView();
        $logString = $logs->toString();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('first message', $logs->logItems[0]->message);


    }


}