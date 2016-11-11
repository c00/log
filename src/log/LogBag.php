<?php
namespace c00\log;

use c00\common\AbstractDatabaseObject;
use \c00\common\CovleDate;
use \c00\common\Helper as H;
/**
 * A collection of LogItems. Commonly grouped for an entire page view.
 *
 * @author Coo
 */
class LogBag extends AbstractDatabaseObject
{

    public $url;
    public $ip;
    public $id;

    /** @var CovleDate */
    public $timestamp;
    /** @var LogItem[]  */
    public $logItems;

    /**
     * LogBag constructor.
     * @param LogItem[] $logItems
     * @param bool $isNew
     */
    public function __construct($logItems = [], $isNew = false) {
        if ($isNew) {
            $this->CreateBagInfo();
        }

        $this->logItems = $logItems;
    }

    public function copy()
    {
        return clone $this;
    }

    public function CreateBagInfo() {
        $this->url = $this->getFullUrl();
        $this->id = "request_" . bin2hex(openssl_random_pseudo_bytes(4));
        $this->timestamp = new CovleDate();

        if (isset($_SERVER['REMOTE_ADDR'])) $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    public function getFullUrl($url_encode = false) {
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

    public function toShowable(){
        $array = H::objectToArray($this);
        $array['timestamp'] = $this->timestamp->toMiliseconds();
        $array['log_items'] = [];
        foreach ($this->logItems as $logItem) {
            $array['log_items'][] = $logItem->toShowable();
        }
        return $array;
    }


    public function toString()
    {
        $strings = [];
        $strings[] = "Url: {$this->url}";
        $strings[] = "IP: {$this->ip}";
        $strings[] = "ID: {$this->id}";
        $strings[] = "Date: {$this->timestamp->toString()}";
        $strings[] = str_repeat("-", 15);

        foreach ($this->logItems as $logItem) {
            $strings[] = $logItem->toString();
        }

        return implode(PHP_EOL, $strings);
    }

}
