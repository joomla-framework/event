## Updating from v3 to v4

The following changes were made to the Event package between v3 and v4.

### Minimum supported PHP version raised

All Framework packages now require PHP 8.3 or newer.

### Deprecated methods removed from `Dispatcher`

Since default Event objects will not be supported anymore, the following methods have been removed from the `Dispatcher` class:

* `setEvent()`
* `addEvent()`
* `hasEvent()`
* `getEvent()`
* `removeEvent()`
* `getEvents()`
* `clearEvents()`
* `countEvents()`
* `getDefaultEvent()`

Furthermore the method `Dispatcher::triggerEvent()` has been removed. Please use `Dispatcher::dispatch()` instead.

### `Dispatcher::dispatch()` requires an Event object

`Dispatcher::dispatch()` in the past had an object with `EventInterface` as optional parameter. With version 4.0, this is now a required parameter.

### `Event::stop()` has been removed

The method `Event::stop()` has been removed. Use `Event::stopPropagation()` instead.
