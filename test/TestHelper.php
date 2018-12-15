<?php

namespace test;


use c00\log\channel\sql\Database;
use c00\log\channel\sql\SqlSettings;
use c00\log\Log;
use c00\log\LogSettings;

class TestHelper {
	const TAG = 'test-helper';

	/** @var Database */
	public $db;
	/** @var LogSettings */
	public $settings;

	public function setupSql() {
		//Setup logging
		$this->settings = new LogSettings('test-settings', __DIR__);
		$this->settings->load();


		/** @var SqlSettings $sqlSettings */
		$sqlSettings = $this->settings->getChannelSettings(SqlSettings::class);

		//DB for getting info
		$this->db = new Database($sqlSettings);
		$this->db->setupTables(true);

		Log::init($this->settings);

		return $this;
	}

	public function addTestLogs() {
		Log::extraDebug(self::TAG, "extra debug message1");
		Log::debug(self::TAG, "debug message1");
		Log::info(self::TAG, "info message1");
		Log::warning(self::TAG, "warning message1");
		Log::error(self::TAG, "error message1");
		Log::extraDebug(self::TAG, "extra debug message2");
		Log::debug(self::TAG, "debug message2");
		Log::info(self::TAG, "info message2");
		Log::warning(self::TAG, "warning message2");
		Log::error(self::TAG, "error message2");
		Log::extraDebug(self::TAG, "extra debug message3");
		Log::debug(self::TAG, "debug message3");
		Log::info(self::TAG, "info message3");
		Log::warning(self::TAG, "warning message3");
		Log::error(self::TAG, "error message3");

		Log::flush();

		return $this;
	}

	public function addMultipleBags() {
		Log::debug(self::TAG, "debug message1");
		Log::info(self::TAG, "info message1");
		Log::flush();

		Log::init($this->settings);
		Log::warning(self::TAG, "warning message1");
		Log::error(self::TAG, "error message1");
		Log::flush();

		Log::init($this->settings);
		Log::warning(self::TAG, "warning message2");
		Log::error(self::TAG, "error message2");
		Log::debug(self::TAG, "debug message2");
		Log::flush();

	}


}