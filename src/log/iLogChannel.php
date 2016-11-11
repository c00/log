<?php
namespace c00\log;

/**
 * Interface for Log Channels. A log channel logs to a different source.
 *
 * @author Coo
 */
interface iLogChannel {
    public function __construct($settings);
    public function flush();
    public function setBag(LogBag $bag);
    
    public function log(LogItem $item);
}
