<?php

namespace c00\log\channel\stdError;

use c00\log\channel\AbstractChannelSettings;

class StdErrorSettings extends AbstractChannelSettings {
	public $class = StdErrorChannel::class;
}