<?php

namespace c00\log;


use c00\common\AbstractSettings;
use c00\log\channel\AbstractChannelSettings;
use c00\log\channel\onScreen\OnScreenSettings;
use c00\log\channel\stdError\StdErrorSettings;
use c00\log\channel\sql\SqlSettings;

class LogSettings extends AbstractSettings
{
    const DEFAULT_KEY = "log-settings";

    /** @var string The default level for new channels */
    public $defaultLevel;

    /** @var AbstractChannelSettings[] */
    public $channelSettings = [];

    public function __construct( string $key = self::DEFAULT_KEY, $path = __DIR__ ) {
	    parent::__construct( $key, $path );
    }

	public function loadDefaults()
    {
        $this->defaultLevel = Log::EXTRA_DEBUG;

        $this->addChannelSettings(OnScreenSettings::new());
	    $this->addChannelSettings(stdErrorSettings::new(Log::ERROR));
    }

    public function addChannelSettings(AbstractChannelSettings $settings) {
    	if (!$settings->level) $settings->level = $this->defaultLevel;

    	$this->channelSettings[get_class($settings)] = $settings;
    }

    public static function new(bool $loadDefaults = true) : LogSettings
    {
        $s = new LogSettings(self::DEFAULT_KEY, __DIR__);
        if ($loadDefaults) $s->loadDefaults();

        return $s;
    }

    public function addSqlChannelSettings($host, $username, $password, $database, $port = null, $level = null, $tablePrefix = null){
        $s = new SqlSettings();
        $s->database = $database;
        $s->username = $username;
        $s->password = $password;
        $s->host = $host;

        if ($port) $s->port = $port;
        if ($level) $s->level = $level;
        if ($tablePrefix) $s->tablePrefix = $tablePrefix;

        $this->addChannelSettings($s);

        return $this;
    }

	/**
	 * @param string $channel
	 *
	 * @return AbstractChannelSettings
	 * @throws LogException
	 */
    public function getChannelSettings(string $channel) : AbstractChannelSettings
    {

    	if (isset($this->channelSettings[$channel])) {
    		return $this->channelSettings[$channel];
	    }

    	throw LogException::new("Unknown channel $channel");
    }


}
