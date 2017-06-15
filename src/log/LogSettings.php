<?php

namespace c00\log;


use c00\common\AbstractSettings;
use c00\common\Helper;
use c00\log\channel\iLogChannel;
use c00\log\channel\LogChannelOnScreen;
use c00\log\channel\LogChannelStdError;

class LogSettings extends AbstractSettings
{
    public $level;

    /** @var ChannelSettings[] */
    public $channelSettings = [];

    public function loadDefaults()
    {
        $this->level = Log::EXTRA_DEBUG;

        $this->channelSettings[] = ChannelSettings::newInstance(LogChannelOnScreen::class,Log::EXTRA_DEBUG);
        $this->channelSettings[] = ChannelSettings::newInstance(LogChannelStdError::class,Log::ERROR);

    }

    public function getChannelSettings($channel) : ChannelSettings
    {

        foreach ($this->channelSettings as $channelSetting) {
            if ($channelSetting->class == $channel) return $channelSetting;
        }
        //if (isset($this->channelSettings[$channel])) return $this->channelSettings[$channel];

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
