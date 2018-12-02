<?php

namespace test;


use c00\log\Log;
use c00\log\LogSettings;

class TestHelper {

	public function setupSql() {
		//run fixture
		$database = "test_log";
		$username = "root";
		$password = "";
		$host = "127.0.0.1";

		//Setup logging
		$settings               = LogSettings::new(false)
		                       ->addSqlChannelSettings($host, $username, $password, $database, null, Log::EXTRA_DEBUG);
		$settings->defaultLevel = Log::INFO;

		/** @var SqlChannelSettings $sqlSettings */
		$sqlSettings = $settings->getChannelSettings(LogChannelSQL::class);

		//Setup database
		$this->pdo = new \PDO(
			"mysql:charset=utf8mb4;host={$sqlSettings->host};dbname={$sqlSettings->database}",
			$sqlSettings->username,
			$sqlSettings->password,
			[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false]
		);

		//empty logs
		$sql = "SET foreign_key_checks = 0; TRUNCATE TABLE `log_bag`; TRUNCATE TABLE `log_item`;";
		$this->pdo->exec($sql);

		//DB for getting info
		$this->db = new Database($sqlSettings);
		$this->db->setupTables();

		Log::init($settings);
	}

	public function getSqlSettings() {

	}
}