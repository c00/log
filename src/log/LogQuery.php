<?php

namespace c00\log;

use c00\common\CovleDate;

/**
 * Class LogQuery
 * Search criteria to find log messages
 *
 * @package c00\log
 */
class LogQuery
{
    const DEFAULT_LIMIT = 100;

    /** @var CovleDate */
    public $since;
    /** @var CovleDate */
    public $until;

    public $includeLevels = [];

    public $page = 0;
    public $limit = self::DEFAULT_LIMIT;

    public function __construct()
    {
    }
}