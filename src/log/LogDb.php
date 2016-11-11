<?php
namespace c00\log;
use \PDO, \PDOException;

/**
 * Handles Database actions for LogChannelDatabase
 *
 * @author Coo
 */
class LogDb {
    /**
     * @var PDO
     */
    var $db;

    function __construct($db_server, $db_name, $db_user, $db_password) {
        //Test connection to database
        try {
            $this->db = new PDO("mysql:host=$db_server;dbname=$db_name", $db_user, $db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
        } catch (PDOException $e) {
            Log::error("Cannot connect to Log Database!");
            Log::error($e->getMessage());
            Log::debug("Error", $e);
        }


    }

    public function saveBag(LogBag $bag) {
        $query = $this->db->prepare("INSERT INTO logbag(url, ip, userId)\n " .
                "VALUES(:url, :ip, :user_id);");
        $query->bindParam(":url", $bag->url);
        $query->bindParam(":ip", $bag->ip);
        $query->bindParam(":user_id", $bag->user_id);

        $query->execute();

        if ($query->rowCount() == 0) {
            return false;
        }
        $bag_id = $this->db->lastInsertId();
        $i = 0;

        foreach ($bag->log_items as $item) {
            $i += $this->saveItem($item, $bag_id);
        }

        return $i;
    }

    public function saveItem(LogItem $item, $bag_id) {
        $data = $item->toSql();

        $query = $this->db->prepare("INSERT INTO logitem(item_id, item, bag_id)\n" .
                "VALUES(:item_id, :data, :bag_id);");
        $query->bindParam(":item_id", $item->id);
        $query->bindParam(":data", $data);
        $query->bindParam(":bag_id", $bag_id);

        $query->execute();

        if ($query->rowCount()) {
            return 1;
        }

        return 0;
    }

    public function getItem($item_id) {
        $query = $this->db->prepare("SELECT i.item_id itemId, i.item item, i.bag_id bagId "
                . "FROM logitem i "
                . "WHERE i.item_id = :id");
        $query->bindParam(":id", $item_id);

        $query->execute();
        $rows = $query->fetchAll();
        if (!empty($rows)) {
            
            $item = LogItem::fromSql($rows[0]['item']);
            $item->bag_id = $rows[0]['bagId'];

            return $item;
        }

        return false;
    }

    public function getBag($bag_id) {
        $query = $this->db->prepare("SELECT b.id bag_id, b.url url, b.ip ip, b.userId userId, b.timestamp timestamp, "
                . "i.item_id itemId, i.item item "
                . "FROM logitem i "
                . "JOIN logbag b on i.bag_id = b.id "
                . "WHERE b.id = :id ");
        $query->bindParam(":id", $bag_id);

        $query->execute();
        $rows = $query->fetchAll();
        if (empty($rows)) {
            return false;
        }

        $bag = new LogBag();
        $bag->id = $rows[0]['bag_id'];
        $bag->url = $rows[0]['url'];
        $bag->ip = $rows[0]['ip'];
        $bag->user_id = $rows[0]['userId'];
        $bag->timestamp = $rows[0]['timestamp'];

        foreach ($rows as $row) {
            $item = LogItem::fromSql($row['item']);
            $item->bag_id = $row['bag_id'];
            
            $bag->log_items[] = $item;
        }

        return $bag;
    }
    
    public function getBagsSince($bag_id){
        //Gets bags with higher number than Bag_id
        $sql = "SELECT id FROM logbag "
                . "WHERE id > :bag_id ";

        $query = $this->db->prepare($sql);
        $query->bindParam(":bag_id", $bag_id);

        $query->execute();
        $rows = $query->fetchAll();
        if (!empty($rows)) {
            $result = [];
            foreach ($rows as $row) {
                $result[] = $this->getBag($row['id']);
            }

            return $result;
        }

        return false;
    }

    public function getBags($user_id = -1, $url = "", $days = 7) {
        $sql = "SELECT id FROM logbag "
                . "WHERE timestamp > (NOW() - INTERVAL :days DAY) ";
        if ($user_id != -1) {
            $sql .= "AND userId = :user_id ";
        }
        if ($url) {
            $sql .= "AND url = :url ";
        }

        $query = $this->db->prepare($sql);
        $query->bindParam(":days", $days);
        if ($user_id != -1) {
            $query->bindParam(":user_id", $user_id);
        }
        if ($url) {
            $query->bindParam(":url", $url);
        }

        $query->execute();
        $rows = $query->fetchAll();
        if (!empty($rows)) {
            $result = [];
            foreach ($rows as $row) {
                $result[] = $this->getBag($row['id']);
            }

            return $result;
        }

        return false;
    }

    public function cleanLog($user_id, $days) {
        //Days is the number of days to retain.
        //Delete items
        $query = $this->db->prepare("DELETE FROM logitem "
                . "WHERE id IN "
                . "(SELECT id FROM logbag "
                . "WHERE timestamp < (NOW() - INTERVAL :days DAY))");
        $query->bindParam(":user_id", $user_id);
        $query->bindParam(":days", $days);

        $query->execute();

        //Delete bags
        $query_bag = $this->db->prepare("DELETE FROM logbag "
                . "WHERE timestamp < (NOW() - INTERVAL :days DAY)");
        $query_bag->bindParam(":user_id", $user_id);
        $query_bag->bindParam(":days", $days);

        $query_bag->execute();

        return $query_bag->rowCount();
    }

}
