<?php
/**
 * Created by PhpStorm.
 * User: coo
 * Date: 07/06/17
 * Time: 11:52
 */

namespace c00\log;


use c00\log\channel\LogChannelOnScreen;

class ChannelSettings
{
    public $level = Log::EXTRA_DEBUG;
    public $load = true;
    public $class = LogChannelOnScreen::class;

    public function __construct()
    {

    }

    public static function newInstance($class, $level, $load = true) : ChannelSettings
    {
        $s = new ChannelSettings();
        $s->class = $class;
        $s->level = $level;
        $s->load = $load;

        return $s;
    }
}