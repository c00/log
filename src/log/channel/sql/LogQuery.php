<?php

namespace c00\log\channel\sql;

use c00\common\CovleDate;

/**
 * Class LogQuery
 * Search criteria to find log messages
 *
 * @package c00\log
 */
class LogQuery
{
    const DEFAULT_PER_PAGE = 100;

    /** @var CovleDate */
    public $since;
    /** @var CovleDate */
    public $until;

    public $levels = [];
    public $tags = [];

    public $page = 0;
    public $perPage = self::DEFAULT_PER_PAGE;

    public function __construct()
    {
    }
}