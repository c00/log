<?php
namespace c00\log;

use c00\log\channel\iLogChannel;
use c00\log\channel\onScreen\OnScreenChannel;

class Log {

    const AUDIT = -1;
    const NO_LOG = 0;
    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;
    const DEBUG = 4;
    const EXTRA_DEBUG = 5;

    const DEFAULT_KEY = "log_default";

    /** @var iLogChannel[] */
    private $channels = [];

    /** @var LogBag*/
    private $logBag;

    /** @var Log Singleton Log Instance */
    private static $instance;

    /** @return Log */
    public static function getInstance(){
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function __construct() {
        //Register to flush on exit.
        register_shutdown_function(function () {
            $logger = Log::getInstance();

            //Check if fatal error
            $error = error_get_last();
            if ($error !== null && $error['type'] === 1){
                //fatal
                Log::error("Fatal Error: " . $error['message'] .
                    " File: " . $error['file'] .
                    " Line: " . $error['line']);
                Log::debug("Error", $error);
            }

            //flush
            $logger->flush();
        });

        //Handle non-fatal errors
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext){
            $error = [];
            $error['type'] = $errno;
            $error['message'] = $errstr;
            $error['file'] = $errfile;
            $error['line'] = $errline;
            $error['context'] = $errcontext;

            Log::error("Error Type: " .$error['type'] .
                " Message: " . $error['message'] .
                " File:" . $error['file'] .
                " Line: " . $error['line']);
            Log::debug("Error", $error);

            //execute standard PHP stuff
            return false;
        });
    }

    public static function init(LogSettings $settings = null){
        if (!$settings) {
            $settings = new LogSettings(self::DEFAULT_KEY, __DIR__);
            $settings->loadDefaults();
        }

        $logger = Log::getInstance();
	    $logger->channels = [];
	    $logger->logBag = new LogBag([], true);

        foreach ($settings->channelSettings as $channelSetting) {
        	$class = $channelSetting->class;
            $channel = new $class($channelSetting);
            $logger->setChannel($channel);
        }
    }

	/**
	 * Sets or adds a channel to log to.
	 *
	 * @param string|null $name 'onScreen', 'stdError', or 'mongo'. The name of the channel. Defaults to the class name.
	 * @param iLogChannel $channel The channel to set or add.
	 */
	public static function setChannel(iLogChannel $channel, $name = null){
		if ($name === null) $name = get_class($channel);

		$logger = Log::getInstance();
		$channel->setBag($logger->logBag->copy());
		$logger->channels[$name] = $channel;
	}

	/**
	 * @param string $name
	 *
	 * @return iLogChannel
	 * @throws LogException
	 */
    public static function getChannel(string $name) {
        $logger = Log::getInstance();
        if (isset($logger->channels[$name])) {
            return $logger->channels[$name];
        }

        throw LogException::new("Channel not found: $name");
    }

    /**
     * Gets the log bag with all the log items.
     *
     * @return LogBag|null
     */
    public static function getLogForView() {
        //Find the onScreen logger.
	    try {
	    	$channel = self::getChannel(OnScreenChannel::class);

	        return $channel->logBag;
	    } catch (LogException $e) {
	    	return null;
	    }
    }

    /**
     * Flushes all current log items. flush() will be called on all channels.
     */
    public static function flush() {
        $logger = Log::getInstance();

        foreach ($logger->channels as $key => $channel) {
            /* @var $channel iLogChannel */
            if (!$channel->flush()){
                error_log("c00 LOGGING ERROR: Can't write to channel: $key");
            }
        }
    }

    public static function levelString($level) {
        switch ($level) {
            case self::DEBUG:
                return "DEBUG";
            case self::ERROR:
                return "ERROR";
            case self::EXTRA_DEBUG:
                return "EXTRA DEBUG";
            case self::INFO:
                return "INFO";
            case self::WARNING:
                return "WARNING";
            case self::AUDIT;
                return "AUDIT";
            default:
                return "???";
        }
    }

    private function logToChannels(LogItem $item) {
        foreach ($this->channels as $channel) {
            /* @var $channel iLogChannel */
            $channel->log($item);
        }
    }

    /**
     * Log an error message.
     *
     * @param string $message The error message to log.
     * @return int Will return 1.
     */
    public static function error($message) {
        $logger = Log::getInstance();

        $trace = debug_backtrace();
        $item = LogItem::newItem(self::ERROR, $message, $trace);

        $logger->logToChannels($item);
        return 1;
    }

    /**
     * Log a warning message.
     *
     * @param string $message The warning message to log.
     * @return int returns 1;
     */
    public static function warning($message) {
        $logger = Log::getInstance();

        $trace = debug_backtrace();
        $item = LogItem::newItem(self::WARNING, $message, $trace);

        $logger->logToChannels($item);
        return 1;
    }

    /**
     * Logs an info message
     *
     * @param string $message The info message to log.
     * @return int returns 1.
     */
    public static function info($message) {
        $logger = Log::getInstance();

        $trace = debug_backtrace();
        $item = LogItem::newItem(self::INFO, $message, $trace);

        $logger->logToChannels($item);
        return 1;
    }

    /**
     * Logs a debug message.
     *
     * Can also log an object as second parameter.
     * @param string $message The debug message to log.
     * @param mixed $o The object to log.
     * @return int
     */
    public static function debug($message, $o = 0) {
        $logger = Log::getInstance();

        $trace = debug_backtrace();
        $item = LogItem::newItem(self::DEBUG, $message, $trace, $o);

        $logger->logToChannels($item);
        return 1;
    }

    /**
     * Logs an extra debug message.
     *
     * Can also log an object as second parameter. Should log verbose information.
     * @param string $message The extra debug message to log.
     * @param mixed $o The object to log.
     * @return int
     */
    public static function extraDebug($message, $o = 0) {
        $logger = Log::getInstance();

        $trace = debug_backtrace();
        $item = LogItem::newItem(self::EXTRA_DEBUG, $message, $trace, $o);

        $logger->logToChannels($item);
        return 1;
    }
}
