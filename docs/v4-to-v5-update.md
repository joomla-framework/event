# Updating from v4 to v5

The following changes were made to the Event package between v4 and v5.

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
