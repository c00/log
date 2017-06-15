<?php
/**
 * Created by PhpStorm.
 * User: Co
 * Date: 18/06/2016
 * Time: 01:12
 */

namespace test;

use c00\log\channel\sql\LogChannelSQL;
use c00\log\channel\sql\SqlChannelSettings;
use c00\log\ChannelSettings;
use c00\log\LogSettings;

class LogSettingsTest extends \PHPUnit_Framework_TestCase
{
    public $tmpDir = __DIR__ . "/../tmp/";
    public $key = "test-settings";

    public function tearDown()
    {
        //Delete temp files.
        unlink($this->tmpDir . $this->key . '.json');

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
        $sqlSettings = new SqlChannelSettings();
        $sqlSettings->database = "test_log";
        $sqlSettings->username = "root";
        $sqlSettings->password = "";
        $sqlSettings->host = "localhost";

        $settings = new LogSettings($this->key, $this->tmpDir);
        $settings->loadDefaults();

        $settings->channelSettings[] = $sqlSettings;
        $settings->save();

        $this->assertTrue(file_exists($this->tmpDir.$this->key . '.json'));

        $loaded = new LogSettings($this->key, $this->tmpDir);
        $loaded->load();

        $loadedSqlChannel = $loaded->getChannelSettings(LogChannelSQL::class);
        $this->assertEquals(SqlChannelSettings::class, get_class($loadedSqlChannel));

    }

}