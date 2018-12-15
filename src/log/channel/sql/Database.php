<?php
namespace c00\log\channel\sql;

use c00\common\AbstractDatabase;
use c00\common\CovleDate;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogItem;
use c00\log\LogQuery;
use c00\QueryBuilder\Qry;
use \PDOException;

/**
 * Handles Database actions for LogChannelDatabase
 *
 * @author Coo
 */
class Database extends AbstractDatabase {

    const TABLE_BAG = 'bag';
    const TABLE_ITEM = 'item';
    const TAG = 'log-database';

    /** @var SqlSettings */
    private $settings = null;

    function __construct(SqlSettings $settings) {
    	parent::__construct();

        $this->settings = $settings;

        //Connect to database
        try {
            $this->connect($settings->host, $settings->username, $settings->password, $settings->database, $settings->port);
        } catch (PDOException $e) {
        	$this->connected = false;
            Log::error(self::TAG, "Cannot connect to Log Database!");
            Log::error(self::TAG, $e->getMessage());
            Log::debug(self::TAG, "Error", $e);
        }
    }

    public function isConnected(): bool {
    	return (bool) $this->connected;
	}

	/** Sets up the tables needed for logging
	 *
	 * @param bool $force Drop and recreate tables even if they already exist.
	 *
	 * @return bool Returns true if tables were created. Returns false if tables already existed.
	 */
    public function setupTables($force = false) : bool
    {
        $tables = [
            $this->getTable("bag"),
            $this->getTable("item"),
        ];

        //Check if the tables exist
        if (!$this->hasTables($tables) || $force){
            $fixture = file_get_contents(__DIR__ . '/fixture.sql');
            $fixture = str_replace("{{PREFIX}}", $this->settings->tablePrefix, $fixture);
            $this->db->exec($fixture);

            return true;
        }

        return false;
    }

    protected function getTable($name){
        return $this->settings->tablePrefix . $name;
    }

    protected function bagExists($bagId){
        $q = Qry::select('id')
            ->from($this->getTable(self::TABLE_BAG))
            ->where('id', '=', $bagId);

        return $this->rowExists($q);
    }

    public function saveBag(LogBag $bag) {

        $this->beginTransaction();

        if (!$this->bagExists($bag->id)){
            $q = Qry::insert($this->getTable(self::TABLE_BAG), $bag);
            $bag->id = $this->insertRow($q);
        }

        foreach ($bag->logItems as $item) {
            $item->bagId = $bag->id;
            $this->saveItem($item);
        }

        $this->commitTransaction();

        return true;
    }

    public function saveItem(LogItem $item) {
        $q = Qry::insert($this->getTable(self::TABLE_ITEM), $item);
        $this->insertRow($q);
    }


    public function getLastBag() : LogBag
    {
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_BAG))
            ->orderBy('date', false)
            ->asClass(LogBag::class);

        /** @var LogBag $bag */
        $bag = $this->getRow($q);

        $bag->logItems = $this->getItems($bag->id);

        return $bag;
    }

    protected function getItems($bagId){
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_ITEM))
            ->where('bagId', '=', $bagId)
            ->orderBy('id', true)
            ->asClass(LogItem::class);

        return $this->getRows($q);
    }

    public function getBag($bagId) {
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_BAG))
            ->where('id', '=', $bagId)
            ->asClass(LogBag::class);

        /** @var LogBag $bag */
        $bag = $this->getRow($q);

        $bag->logItems = $this->getItems($bag->id);

        return $bag;
    }

    public function getLastBags($number) {
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_BAG))
            ->orderBy('date', false)
            ->limit($number)
            ->asClass(LogBag::class);

        /** @var LogBag[] $bags */
        $bags = $this->getRows($q);

        foreach ($bags as $bag) {
            $bag->logItems = $this->getItems($bag->id);
        }

        return $bags;
    }

    public function getBagsSince(CovleDate $since, CovleDate $until = null, $limit = 100, $offset = 0) {
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_BAG))
            ->where('date', '>', $since->toSeconds())
            ->orderBy('date', false)
            ->limit($limit, $offset)
            ->asClass(LogBag::class);

        if ($until){
            $q->where('date', '<', $until->toSeconds());
        }

        /** @var LogBag[] $bags */
        $bags = $this->getRows($q);

        foreach ($bags as $bag) {
            $bag->logItems = $this->getItems($bag->id);
        }

        return $bags;
    }

    public function queryBags(LogQuery $query) {
        $offset = $query->limit * $query->page;
        $q = Qry::select()
            ->fromClass(LogBag::class, $this->getTable(self::TABLE_BAG), 'b')
            ->joinClass(LogItem::class, $this->getTable(self::TABLE_ITEM), 'i', 'b.id', '=', 'i.bagId')
            ->orderBy('b.date', false)
            ->limit($query->limit, $offset);

        if ($query->since){
            $q->where('b.date', '>', $query->since->toSeconds());
        }

        if ($query->until){
            $q->where('b.date', '<', $query->until->toSeconds());
        }

        if (count($query->includeLevels) > 0){
            $q->whereIn('i.level', $query->includeLevels);
        }

        /** @var LogBag[] $bags */
        $objects = $this->getObjects($q);
        /** @var LogBag[] $bags */
        $bags = $objects['b'] ?? [];
        /** @var LogItem[] $items */
        $items = $objects['i'] ?? [];

        foreach ($items as $item) {
            if (isset($bags[$item->bagId])) $bags[$item->bagId]->logItems[] = $item;
        }

        return array_values($bags);
    }
}
