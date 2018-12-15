<?php
namespace c00\log\channel\sql;

use c00\common\AbstractDatabase;
use c00\log\Log;
use c00\log\LogBag;
use c00\log\LogItem;
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

	/**
	 * @param LogBag $bag
	 *
	 * @return bool
	 * @throws \Exception
	 */
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

	/**
	 * @param LogItem $item
	 *
	 * @throws \Exception
	 */
    public function saveItem(LogItem $item) {
        $q = Qry::insert($this->getTable(self::TABLE_ITEM), $item);
        $this->insertRow($q);
    }

	/**
	 * @return LogBag
	 * @throws \c00\QueryBuilder\QueryBuilderException When row doesn't exist
	 */
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

	/**
	 * @param $bagId
	 *
	 * @return array
	 * @throws \c00\QueryBuilder\QueryBuilderException
	 */
    protected function getItems($bagId){
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_ITEM))
            ->where('bagId', '=', $bagId)
            ->orderBy('id', true)
            ->asClass(LogItem::class);

        return $this->getRows($q);
    }

	/**
	 * @param $bagId
	 *
	 * @return LogBag
	 * @throws \c00\QueryBuilder\QueryBuilderException
	 */
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

	/**
	 * @param int[] $ids
	 *
	 * @return LogBag[]
	 * @throws \c00\QueryBuilder\QueryBuilderException
	 */
    private function getBagsByIds(array $ids) {
    	if (empty($ids)) return [];

		$q = Qry::select()
				->fromClass(LogBag::class, $this->getTable(self::TABLE_BAG), 'b')
				->joinClass(LogItem::class, $this->getTable(self::TABLE_ITEM), 'li', 'b.id', '=', 'li.bagId')
				->whereIn('b.id', $ids)
				->orderBy('b.date', false)
				;

		$objects = $this->getObjects($q);

		/** @var LogBag[] $bags */
		$bags = $objects['b'] ?? [];
		/** @var LogItem[] $items */
		$items = $objects['li'] ?? [];

		foreach ( $items as $item ) {
			$bags[$item->bagId]->logItems[] = $item;
		}

		return array_values($bags);
	}

	/**
	 * @param LogQuery $query
	 *
	 * @return LogBag[]
	 * @throws \c00\QueryBuilder\QueryBuilderException
	 */
    public function queryBags(LogQuery $query) {
        $offset = $query->limit * $query->page;

        $q = Qry::select('b.id')
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

        if ( count($query->levels) > 0){
            $q->whereIn('i.level', $query->levels);
        }

        if (count($query->tags) > 0) {
        	$q->whereIn('i.tag', $query->tags);
		}

        $ids = $this->getValues($q);

        return $this->getBagsByIds($ids);
    }
}
