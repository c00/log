#log
This is a very experimental logging 'framework'. Adapted from musings of earlier projects, it's currently really only used to log some stuff and show it on screen.

Documentation is lacking, so unless you're interested in seeing how I code, this is probably not for you.

It has the potential to easily log to different channels (DB, on screen, stdError, etc).

#Usage
    Log::init();
    Log::debug("A debug message");
    Log::error("Something went wrong!");
    
    echo Log::getLogForView()->toString();
    
Will output:
```
Url: http://example.com
IP: 127.0.0.1
ID: request_383b556e
Date: 2016-11-11 03:31:02
---------------
2016-11-11 03:31:02 DEBUG: A debug message
    D:\www\log\test\LogTest.php line: 19
2016-11-11 03:31:02 ERROR: Something went wrong!
    D:\www\log\test\LogTest.php line: 20
```