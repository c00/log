<?php

namespace c00\log\channel\sql;

use c00\log\channel\AbstractChannelSettings;

class SqlSettings extends AbstractChannelSettings
{
	public $class = SqlChannel::class;

    public $username = 'root';
    public $password = '';
    public $host = 'localhost';
    public $database = 'log_default';
    public $port = null;
    public $tablePrefix = "log_";
}