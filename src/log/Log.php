<?php
namespace c00\log;

class Log {

    const AUDIT = -1;
    const NO_LOG = 0;
    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;
    const DEBUG = 4;
    const EXTRA_DEBUG = 5;

    /** @var iLogChannel[] */
    private $channels = [];

    /** @var LogBag*/
    var $logbag;

    /** @var Log Singleton Log Instance */
    private static $instance;

    /** @return Log */
    public static function getInstance(){
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Set the log level of a specific channel
     * @param $level int Constant of iLogChannel
     * @param string $channel Defaults to on screen channel
     * @returns bool True if successful, false if channel doesn't exist.
     */
    public static function setLogLevel($level, $channel = iLogChannel::CHANNEL_ON_SCREEN){
        $logger = self::getInstance();

        if (!isset($logger->channels[$channel])) return false;

        $logger->channels[$channel]->level = $level;

        return true;
    }

    public function __construct() {
        $this->logbag = new LogBag([], true);

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

    /**
     * Sets or adds a channel to log to.
     *
     * @param string $name 'onScreen', 'stdError', or 'mongo'. The name of the channel.
     * @param iLogChannel $channel The channel to set or add.
     */
    public static function setChannel($name, iLogChannel $channel){
        $logger = Log::getInstance();
        $channel->setBag($logger->logbag->copy());
        $logger->channels[$name] = $channel;
    }

    public static function init($loadDefaultChannels = true){
        $logger = Log::getInstance();

        if ($loadDefaultChannels){
            $logger->setChannel(iLogChannel::CHANNEL_ON_SCREEN, new LogChannelOnScreen(["level" => self::EXTRA_DEBUG]));
            $logger->setChannel(iLogChannel::CHANNEL_STD_ERROR, new LogChannelStdError(["level" => self::ERROR]));
        }
    }

    public static function getChannel($name) {
        $logger = Log::getInstance();
        if (isset($logger->channels[$name])) {
            return $logger->channels[$name];
        }

        return false;
    }

    /**
     * Gets the logbag with all the log items.
     *
     * @return LogBag|null
     */
    public static function getLogForView() {
        $logger = Log::getInstance();

        //Find the on screen logger.
        if (!isset($logger->channels['onScreen']) || !isset($logger->channels['onScreen']->logbag)) {
            return null;
        }

        $channel = $logger->channels['onScreen'];

        return $channel->logbag;
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

    public function logToChannels(LogItem $item) {
        foreach ($this->channels as $channel) {
            /* @var $channel iLogChannel */
            $channel->log($item);
        }
    }

    public function auditToChannels(AuditItem $item) {
        foreach ($this->channels as $channel) {
            /* @var $channel iLogChannel */
            $channel->audit($item);
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
        $item = new LogItem(self::ERROR, $message, $trace);

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
        $item = new LogItem(self::WARNING, $message, $trace);

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
        $item = new LogItem(self::INFO, $message, $trace);

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
        $item = new LogItem(self::DEBUG, $message, $trace, $o);

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
        $item = new LogItem(self::EXTRA_DEBUG, $message, $trace, $o);

        $logger->logToChannels($item);
        return 1;
    }

}
