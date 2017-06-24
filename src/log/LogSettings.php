<?php

namespace c00\log;


use c00\common\AbstractSettings;
use c00\common\Helper;
use c00\log\channel\iLogChannel;
use c00\log\channel\LogChannelOnScreen;
use c00\log\channel\LogChannelStdError;
use c00\log\channel\sql\SqlChannelSettings;

class LogSettings extends AbstractSettings
{
    const DEFAULT_KEY = "log-settings";

    public $level;

    /** @var ChannelSettings[] */
    public $channelSettings = [];

    public function loadDefaults()
    {
        $this->level = Log::EXTRA_DEBUG;

        $this->channelSettings[] = ChannelSettings::newInstance(LogChannelOnScreen::class,Log::EXTRA_DEBUG);
        $this->channelSettings[] = ChannelSettings::newInstance(LogChannelStdError::class,Log::ERROR);

    }

    public static function newInstance(bool $loadDefaults = true) : LogSettings
    {
        $s = new LogSettings(self::DEFAULT_KEY, __DIR__);
        if ($loadDefaults) $s->loadDefaults();

        return $s;
    }

    public function addSqlChannelSettings($host, $username, $password, $database, $port = null, $level = Log::DEBUG, $tablePrefix = "log_"){
        $s = new SqlChannelSettings();
        $s->database = $database;
        $s->username = $username;
        $s->password = $password;
        $s->host = $host;
        $s->port = $port;
        $s->level = $level;
        $s->tablePrefix = $tablePrefix;

        $this->channelSettings[] = $s;

        return $this;
    }

    public function getChannelSettings($channel) : ChannelSettings
    {

        foreach ($this->channelSettings as $channelSetting) {
            if ($channelSetting->class == $channel) return $channelSetting;
        }

        return new ChannelSettings();
    }

    public function Load(){
        parent::load();

        $settingsArrays = $this->channelSettings;

        $this->channelSettings = [];

        //Cast ChannelSettings
        foreach ($settingsArrays as $channelSettingArray) {
            $class = $channelSettingArray['settingsClass'];
            $channelSettings = new $class();
            Helper::copyArrayPropertiesToObject($channelSettingArray, $channelSettings);

            $this->channelSettings[] = $channelSettings;
        }
    }


}
