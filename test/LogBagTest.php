<?php

namespace test;

use c00\log\channel\onScreen\OnScreenChannel;
use c00\log\channel\onScreen\OnScreenSettings;
use c00\log\channel\sql\SqlChannel;
use c00\log\channel\stdError\StdErrorChannel;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogSettings;
use PHPUnit\Framework\TestCase;

class LogBagTest extends TestCase
{

	public function testUrl() {
		$l = new LogBag([], true);
		$this->assertContains('log/vendor/phpunit/phpunit/phpunit', $l->getFullUrl());
	}
}