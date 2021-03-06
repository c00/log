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
use c00\log\channel\sql\LogChannelSQL;
use c00\log\channel\sql\SqlChannelSettings;
use c00\log\ChannelSettings;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogSettings;

class LogTest extends \PHPUnit_Framework_TestCase
{

    public function testInit(){
        Log::init();
        Log::debug("first message");
        Log::info("info message");
        Log::error("error message");

        $logs = Log::getLogForView();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('first message', $logs->logItems[0]->message);
        $this->assertEquals('info message', $logs->logItems[1]->message);
        $this->assertEquals('error message', $logs->logItems[2]->message);
        $this->assertEquals(Log::DEBUG, $logs->logItems[0]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[1]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[2]->level);
    }

    public function testChannels(){
        Log::init();

        $onScreen = Log::getChannel(LogChannelOnScreen::class);
        $this->assertTrue($onScreen instanceof LogChannelOnScreen);

        $stdError = Log::getChannel(LogChannelStdError::class);
        $this->assertTrue($stdError instanceof LogChannelStdError);
    }

    public function testChannels2(){
        $settings = new LogSettings('app', __DIR__);
        $settings->level = Log::INFO;

        //On screen channel
        $settings->channelSettings[] = ChannelSettings::newInstance(LogChannelOnScreen::class, $settings->level);

        //SQL channel
        $settings->channelSettings[] = new SqlChannelSettings();

        Log::init($settings);

        $onScreen = Log::getChannel(LogChannelOnScreen::class);
        $this->assertTrue($onScreen instanceof LogChannelOnScreen);

        $stdError = Log::getChannel(LogChannelStdError::class);
        $this->assertNull($stdError);

        $sql = Log::getChannel(LogChannelSQL::class);
        $this->assertTrue($sql instanceof LogChannelSQL);
    }


}