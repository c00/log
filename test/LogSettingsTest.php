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
use c00\log\Log;
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
        $username = "root";
        $password = "";
        $host = "localhost";

        $settings = LogSettings::newInstance()
            ->addSqlChannelSettings($database, $username, $password, $host);

        Log::init($settings);
    }

}