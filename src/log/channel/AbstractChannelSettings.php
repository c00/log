<?php

namespace c00\log\channel;

abstract class AbstractChannelSettings
{
    public $level;
    public $load = true;

    public function __construct()
    {

    }

	/**
	 * @param string $level
	 *
	 * @return AbstractChannelSettings
	 */
    public static function new($level = null)
    {
    	$s = new static();
    	$s->level = $level;

    	return $s;
    }
}