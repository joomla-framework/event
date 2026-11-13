# Updating from v4 to v5

The following changes were made to the Event package between v4 and v5.

## The 2.0 deprecations have been removed

The API deprecated in 2.0 and announced for removal in 5.0 is gone:

| Removed | Replacement |
|---|---|
| `Dispatcher::setEvent()`, `addEvent()`, `hasEvent()`, `getEvent()`, `removeEvent()`, `getEvents()`, `clearEvents()`, `countEvents()` and the `$events` property | Create the event object yourself and pass it to `dispatch()` |
| `Dispatcher::triggerEvent()` | `dispatch()` |
| `Event::stop()` | `stopPropagation()` |

The `$event` argument of `DispatcherInterface::dispatch()` and `Dispatcher::dispatch()` is now required
and no longer nullable. The dispatcher does not create default event objects any more:

```php
// v4 (deprecated)
$dispatcher->dispatch('onSomething');
$dispatcher->triggerEvent('onSomething');

// v5
$dispatcher->dispatch('onSomething', new Event('onSomething'));
```

Classes implementing `DispatcherInterface` may keep `?EventInterface $event = null` in their own
signature (PHP allows an implementation to make a parameter optional), but callers can no longer rely
on it.
