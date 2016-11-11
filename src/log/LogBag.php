<?php
namespace c00\log;
use \c00\common\CovleDate;
use \c00\common\Helper as H;
/**
 * A collection of LogItems. Commonly grouped for an entire page view.
 *
 * @author Coo
 */
class LogBag {

    var $url, $user_id, $ip, $requestId, $id;
    /**
     * @var CovleDate
     */
    var $timestamp;

    var $log_items;
    var $audit_items;

    function __construct($log_items = [], $is_new = false) {
        if ($is_new) {
            $this->CreateBagInfo();
        }

        $this->log_items = $log_items;
    }

    function copy()
    {
        return clone $this;
    }

    function CreateBagInfo() {
        $this->url = $this->getFullUrl();
        $this->requestId = uniqid("request");
        $this->timestamp = new CovleDate();

        //todo: fix this.
        global $user;
        if (is_array($user)) {
            $this->user_id = $user['id'];
        } else {
            $this->user_id = 0;
        }

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    function getFullUrl($url_encode = false) {
        if (isset($_SERVER['HTTPS'])) {
            $result = "https://";
        } else {
            $result = "http://";
        }
        $result .= filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL)
            . filter_input(INPUT_SERVER, "REQUEST_URI", FILTER_SANITIZE_URL);

        if ($url_encode) {
            return urlencode($result);
        }
        return $result;
    }

    function toMongo(){
        $array = H::objectToArray($this);
        $array['timestamp'] = $this->timestamp->toMongoDate();
        $array['log_items'] = [];

        foreach ($this->log_items as $log_item) {
            /**
             * @var $log_item LogItem
             */
            $array['log_items'][] = $log_item->toMongo();
        }

        //todo: create one for Audit Items (but unnecesary atm)
        return $array;
    }

    static function fromMongo(array $array){
        H::fixMongoIdToString($array);

        $bag = new LogBag();
        H::copyArrayPropertiesToObject($array, $bag);
        if (isset($array['timestamp'])) $bag->timestamp = CovleDate::fromMongoDate($array['timestamp']);

        //Iterate through logItems
        if (isset($array['log_items'])){
            $bag->log_items = [];
            foreach($array['log_items'] as $item){
                $bag->log_items[] = LogItem::fromMongo($item);
            }
        }

        //Iterate through Audit Items
        if (isset($array['audit_items'])){
            $bag->audit_items = [];
            foreach($array['audit_items'] as $item){
                $bag->audit_items[] = AuditItem::fromMongo($item);
            }
        }

        return $bag;
    }

    function toShowable(){
        $array = H::objectToArray($this);
        $array['timestamp'] = $this->timestamp->toMiliseconds();
        $array['log_items'] = [];
        foreach ($this->log_items as $log_item) {
            /**
             * @var $log_item LogItem
             */

            $array['log_items'][] = $log_item->toShowable();
        }
        return $array;
    }

}
