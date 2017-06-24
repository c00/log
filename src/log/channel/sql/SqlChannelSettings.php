<?php
/**
 * Created by PhpStorm.
 * User: coo
 * Date: 07/06/17
 * Time: 11:57
 */

namespace c00\log\channel\sql;


use c00\log\ChannelSettings;
use c00\log\Log;

class SqlChannelSettings extends ChannelSettings
{
    public $username = 'root';
    public $password = '';
    public $host = 'localhost';
    public $database = 'log_default';
    public $port = null;
    public $tablePrefix = "";

    public function __construct()
    {
        $this->class = LogChannelSQL::class;
        $this->settingsClass = self::class;
    }

}