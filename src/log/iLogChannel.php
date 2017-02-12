<?php
namespace c00\log;

/**
 * Interface for Log Channels. A log channel logs to a different source.
 *
 * @author Coo
 */
interface iLogChannel {
    const CHANNEL_ON_SCREEN = 'onScreen';
    const CHANNEL_STD_ERROR = 'stdError';

    public function __construct($settings);
    public function flush();
    public function setBag(LogBag $bag);
    public function setLevel($level);
    
    public function log(LogItem $item);
}
