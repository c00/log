<?php

namespace test;

use c00\log\channel\onScreen\OnScreenChannel;
use c00\log\channel\onScreen\OnScreenSettings;
use c00\log\channel\sql\SqlChannel;
use c00\log\channel\stdError\StdErrorChannel;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogSettings;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
	const TAG_TEST = 'test';

	public function testInit(){
        Log::init();
        Log::debug(self::TAG_TEST, "first message");
        Log::info(self::TAG_TEST, "info message");
        Log::error(self::TAG_TEST, "error message");

        Log::log(Log::EXTRA_DEBUG, self::TAG_TEST, "Extra debug message through log function");

        $logs = Log::getLogForView();

        $this->assertTrue($logs instanceof LogBag);

        $this->assertEquals('first message', $logs->logItems[0]->message);
        $this->assertEquals('info message', $logs->logItems[1]->message);
        $this->assertEquals('error message', $logs->logItems[2]->message);
		$this->assertEquals('Extra debug message through log function', $logs->logItems[3]->message);
        $this->assertEquals(Log::DEBUG, $logs->logItems[0]->level);
        $this->assertEquals(Log::INFO, $logs->logItems[1]->level);
        $this->assertEquals(Log::ERROR, $logs->logItems[2]->level);
		$this->assertEquals(Log::EXTRA_DEBUG, $logs->logItems[3]->level);

		$this->assertEquals(self::TAG_TEST, $logs->logItems[0]->tag);
		$this->assertEquals(self::TAG_TEST, $logs->logItems[1]->tag);
		$this->assertEquals(self::TAG_TEST, $logs->logItems[2]->tag);
		$this->assertEquals(self::TAG_TEST, $logs->logItems[3]->tag);
	}

	/**
	 * @throws \c00\log\LogException
	 */
    public function testChannels(){
        Log::init();

        $onScreen = Log::getChannel(OnScreenChannel::class);
        $this->assertTrue($onScreen instanceof OnScreenChannel);

        $stdError = Log::getChannel(StdErrorChannel::class);
        $this->assertTrue( $stdError instanceof StdErrorChannel);
    }

	/**
	 * @throws \c00\log\LogException
	 */
    public function testChannels2(){
        $settings = new LogSettings('app', __DIR__);
        $settings->defaultLevel = Log::INFO;

        //On screen channel
	    $settings->addChannelSettings(OnScreenSettings::new());

        //SQL channel
	    $database = "test_log";
	    $username = "coo";
	    $password = "123";
	    $host = "127.0.0.1";
		$settings->addSqlChannelSettings($host, $username, $password, $database);

        Log::init($settings);

        $onScreen = Log::getChannel(OnScreenChannel::class);
        $this->assertTrue($onScreen instanceof OnScreenChannel);

        $sql = Log::getChannel(SqlChannel::class);
        $this->assertTrue( $sql instanceof SqlChannel);

        $this->expectExceptionMessage("Channel not found");
	    Log::getChannel(StdErrorChannel::class);
    }


}