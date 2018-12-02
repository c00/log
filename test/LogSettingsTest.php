<?php

namespace test;

use c00\log\channel\sql\LogChannelSQL;
use c00\log\channel\sql\SqlChannelSettings;
use c00\log\ChannelSettings;
use c00\log\Log;
use c00\log\LogSettings;
use PHPUnit\Framework\TestCase;

class LogSettingsTest extends TestCase
{
    public $tmpDir = __DIR__ . "/../tmp/";
    public $key = "test-settings";

    public function tearDown()
    {
        //Delete temp files.
        $file = $this->tmpDir . $this->key . '.json';
        if (file_exists($file)) unlink($file);

        parent::tearDown();
    }

    public function testSaving(){
        $settings = new LogSettings($this->key, $this->tmpDir);
        $settings->loadDefaults();
        $settings->save();

        $this->assertTrue(file_exists($this->tmpDir.$this->key . '.json'));

        $loaded = new LogSettings($this->key, $this->tmpDir);
        $loaded->load();

        $this->assertEquals($settings->level, $loaded->level);

        $this->assertEquals(ChannelSettings::class, get_class($loaded->channelSettings[0]));
    }

    public function testSqlChannel(){
        $database = "test_log";
        $username = "root";
        $password = "foo";
        $host = "localhost";

        $settings = new LogSettings($this->key, $this->tmpDir);
        $settings->loadDefaults();
        $settings->addSqlChannelSettings($database, $username, $password, $host);
        $settings->save();

        $this->assertTrue(file_exists($this->tmpDir.$this->key . '.json'));

        $loaded = new LogSettings($this->key, $this->tmpDir);
        $loaded->load();

        /** @var SqlChannelSettings $loadedSqlChannelSettings */
        $loadedSqlChannelSettings = $loaded->getChannelSettings(LogChannelSQL::class);
        $this->assertEquals(SqlChannelSettings::class, get_class($loadedSqlChannelSettings));
        $this->assertEquals("foo", $loadedSqlChannelSettings->password);

    }

    public function testSqlShorthand(){
        $database = "test_log";
        $username = "coo";
        $password = "123";
        $host = "127.0.0.1";

        $settings = LogSettings::newInstance()
            ->addSqlChannelSettings($host, $username, $password, $database);

        Log::init($settings);

        //Assert that we didn't fail
	    $this->assertTrue(true);
    }

}