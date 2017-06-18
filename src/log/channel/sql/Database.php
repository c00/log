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

    function __construct(SqlChannelSettings $settings) {

        //Connect to database
        try {
            $this->connect($settings->host, $settings->username, $settings->password, $settings->database);
        } catch (PDOException $e) {
            Log::error("Cannot connect to Log Database!");
            Log::error($e->getMessage());
            Log::debug("Error", $e);
        }
    }

    private function bagExists($bagId){
        $q = Qry::select('id')
            ->from(self::TABLE_BAG)
            ->where('id', '=', $bagId);

        return $this->rowExists($q);
    }

    public function saveBag(LogBag $bag) {

        if (!$this->bagExists($bag->id)){
            $q = Qry::insert(self::TABLE_BAG, $bag);
            $this->insertRow($q);
        }

        foreach ($bag->logItems as $item) {
            $item->bagId = $bag->id;
            $this->saveItem($item);
        }

        return true;
    }

    public function saveItem(LogItem $item) {
        $q = Qry::insert(self::TABLE_ITEM, $item);
        $this->insertRow($q);
    }


    public function getLastBag() : LogBag
    {
        $q = Qry::select()
            ->from(self::TABLE_BAG)
            ->orderBy('date', false)
            ->asClass(LogBag::class);

        /** @var LogBag $bag */
        $bag = $this->getRow($q);

        $bag->logItems = $this->getItems($bag->id);

        return $bag;
    }

    private function getItems($bagId){
        $q = Qry::select()
            ->from(self::TABLE_ITEM)
            ->where('bagId', '=', $bagId)
            ->orderBy('order', true)
            ->asClass(LogItem::class);

        return $this->getRows($q);
    }

    public function getBag($bagId) {
        $q = Qry::select()
            ->from(self::TABLE_BAG)
            ->where('id', '=', $bagId)
            ->asClass(LogBag::class);

        /** @var LogBag $bag */
        $bag = $this->getRow($q);

        $bag->logItems = $this->getItems($bag->id);

        return $bag;
    }

    public function getLastBags($number) {
        $q = Qry::select()
            ->from(self::TABLE_BAG)
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

    public function getBagsSince(CovleDate $since, CovleDate $until = null) {
        $q = Qry::select()
            ->from(self::TABLE_BAG)
            ->where('date', '>', $since->toSeconds())
            ->orderBy('date', false)
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
