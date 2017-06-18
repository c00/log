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
    public $id;
    public $url;
    public $ip;
    public $userId;

    /** @var CovleDate */
    public $date;
    /** @var LogItem[]  */
    public $logItems;

    protected $_dataTypes = [
        'date' => CovleDate::class
    ];

    protected $_ignore = ['logItems'];

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
        $this->id = "req_" . bin2hex(random_bytes(12));
        $this->date = new CovleDate();

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
        $array['date'] = $this->date->toMiliseconds();
        $array['logItems'] = [];
        foreach ($this->logItems as $logItem) {
            $array['logItems'][] = $logItem->toShowable();
        }
        return $array;
    }


    public function toString()
    {
        $strings = [];
        $strings[] = "Url: {$this->url}";
        $strings[] = "IP: {$this->ip}";
        $strings[] = "ID: {$this->id}";
        $strings[] = "Date: {$this->date->toString()}";
        $strings[] = str_repeat("-", 15);

        foreach ($this->logItems as $logItem) {
            $strings[] = $logItem->toString();
        }

        return implode(PHP_EOL, $strings);
    }

}
