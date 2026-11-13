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

## `AbstractEvent` no longer implements `Serializable`

PHP deprecated the `Serializable` interface in 8.1. `AbstractEvent` already implemented
`__serialize()` and `__unserialize()`, which PHP prefers, so the interface and its
`serialize()`/`unserialize()` methods were only legacy surface. They have been removed.

`serialize($event)` and `unserialize($data)` keep working. Code that called the methods directly
must use the PHP functions instead:

```php
// v4
$data = $event->serialize();
$event->unserialize($data);

// v5
$data  = serialize($event);
$event = unserialize($data);
```

Events serialized with v4 use the `O:` format of `__serialize()` already and can be unserialized
with v5. Data in the old `C:` format, written through `Serializable` (by 1.x, or on PHP before 7.4),
can no longer be restored: it yields an event without name and arguments.
