<?php
namespace c00\log\channel;
use c00\log\LogBag;
use c00\log\LogItem;

/**
 * Interface for Log Channels. A log channel logs to a different source.
 *
 * @author Coo
 */
interface iLogChannel {
	/**
	 * iLogChannel constructor.
	 *
	 * @param AbstractChannelSettings $settings
	 */
    public function __construct($settings);

    /** Flushes any unsaved log messages to the channel (e.g., Database)
     * @return bool True on success, False on error.
     */
    public function flush();

    public function setBag(LogBag $bag);

    /** Logs an item or message to a channel.
     * @param LogItem $item
     * @return bool True if needed to be logged, false if log level wasn't sufficient.
     */
    public function log(LogItem $item);
}
