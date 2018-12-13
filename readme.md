# log

This is a very experimental logging 'framework'. It currently can log to screen / stdError and SQL.

Documentation is lacking, so unless you're interested in seeing how I code, this is probably not for you.

It has the potential to easily log to different channels (DB, on screen, stdError, etc).

# New in 2.x

Log messages have tags
Log messages are ordered in a cleaner way
Class names are more consistent

# Upgrading from 0.x to 2.x

0.x never came to a full 1.x, but had its limitations. So I created 2.x.

## Update your settings files with new class names and channel keys

The following class names have changed:

- `c00\log\channel\LogChannelOnScreen` is now `c00\log\channel\onScreen\OnScreenChannel`
- `c00\log\channel\LogChannelStdError` is now `c00\log\channel\stdError\StdErrorChannel`
- `c00\log\channel\sql\LogChannelSQL` is now `c00\log\channel\sql\SqlChannel`

Each channel now has its own settings class.

- `c00\log\ChannelSettings` is now `c00\log\AbstractChannelSettings`
- `c00\log\channel\onScreen\OnScreenChannel` now uses `c00\log\channel\onScreen\OnScreenSettings`
- `c00\log\channel\stdError\StdErrorChannel` now uses `c00\log\channel\stdError\StdErrorSettings`
- `c00\log\channel\sql\SqlChannel` now uses `c00\log\channel\sql\SqlSettings`

The channels are now assoc arrays, with the class name of the settings as key. e.g.

```
[...]
"channelSettings": {
    "c00\\log\\channel\\onScreen\\OnScreenSettings": {
      "level": 5,
      "load": true,
      "class": "c00\\log\\channel\\onScreen\\OnScreenChannel",
      "__class": "c00\\log\\channel\\onScreen\\OnScreenSettings"
    },
    "c00\\log\\channel\\stdError\\StdErrorSettings": {
      "level": 1,
      "load": true,
      "class": "c00\\log\\channel\\stdError\\StdErrorChannel",
      "__class": "c00\\log\\channel\\stdError\\StdErrorSettings"
    }
},
[...]
```

## Table structure is updated

Be sure to recreate the tables. The convenience method `c00\log\channel\sql\Database::setupTables(true)` will force a drop and recreation of the tables.

If you want to retain your old logs, the easiest thing to do, is to update the prefix in the settings file, so that a new set of tables will be created.

## New function signatures

Because the Tags functionality has been added, the signatures of all the log functions are updated. The following functions have been updated to include a `$tag` param as the first parameter:

- `Log::extraDebug()`
- `Log::debug()`
- `Log::info()`
- `Log::warning()`
- `Log::error()`

# Usage

```
Log::init();
Log::debug("startup", A debug message");
Log::error("startup", "Something went wrong!");

echo Log::getLogForView()->toString();
``` 
   
Will output:
```
Url: http://example.com
IP: 127.0.0.1
ID: request_383b556e
Date: 2016-11-11 03:31:02
---------------
2016-11-11 03:31:02 DEBUG: [startup] A debug message
    D:\www\log\test\LogTest.php line: 19
2016-11-11 03:31:02 ERROR: [startup] Something went wrong!
    D:\www\log\test\LogTest.php line: 20
```

# todo 

Among other things:
- Create a front-end
- Allow the use of multiple channels of the same type (e.g. but with different log levels)