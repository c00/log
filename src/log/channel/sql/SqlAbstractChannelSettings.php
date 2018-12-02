<?php

namespace c00\log\channel\sql;

use c00\log\channel\AbstractChannelSettings;

class SqlAbstractChannelSettings extends AbstractChannelSettings
{
    public $username = 'root';
    public $password = '';
    public $host = 'localhost';
    public $database = 'log_default';
    public $port = null;
    public $tablePrefix = "";

}