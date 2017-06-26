<?php
namespace c00\log\channel\sql;

use c00\common\AbstractDatabase;
use c00\common\CovleDate;
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

    /** @var SqlChannelSettings */
    private $settings = null;

    function __construct(SqlChannelSettings $settings) {

        $this->settings = $settings;

        //Connect to database
        try {
            $this->connect($settings->host, $settings->username, $settings->password, $settings->database, $settings->port);
        } catch (PDOException $e) {
            Log::error("Cannot connect to Log Database!");
            Log::error($e->getMessage());
            Log::debug("Error", $e);
        }
    }

    /** Sets up the tables needed for logging
     *
     * @return bool Returns true if tables were created. Returns false if tables already existed.
     */
    public function setupTables() : bool
    {
        $tables = [
            $this->getTable("bag"),
            $this->getTable("item"),
        ];

        //Check if the tables exist
        if (!$this->hasTables($tables)){
            $fixture = file_get_contents(__DIR__ . '/fixture.sql');
            $fixture = str_replace("{{PREFIX}}", $this->settings->tablePrefix, $fixture);
            $this->db->exec($fixture);

            return true;
        }

        return false;
    }

    private function getTable($name){
        return $this->settings->tablePrefix . $name;
    }

    private function bagExists($bagId){
        $q = Qry::select('id')
            ->from($this->getTable(self::TABLE_BAG))
            ->where('id', '=', $bagId);

        return $this->rowExists($q);
    }

    public function saveBag(LogBag $bag) {

        if (!$this->bagExists($bag->id)){
            $q = Qry::insert($this->getTable(self::TABLE_BAG), $bag);
            $this->insertRow($q);
        }

        foreach ($bag->logItems as $item) {
            $item->bagId = $bag->id;
            $this->saveItem($item);
        }

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

    private function getItems($bagId){
        $q = Qry::select()
            ->from($this->getTable(self::TABLE_ITEM))
            ->where('bagId', '=', $bagId)
            ->orderBy('order', true)
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
}
