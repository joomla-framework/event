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

## Native parameter, return and property types

The interfaces and classes now declare native types instead of docblock types, and
`#[\ReturnTypeWillChange]` is gone. Implementations and subclasses must use compatible signatures.

| Method | v5 signature |
|---|---|
| `EventInterface::getArgument()` | `getArgument(string\|int $name, mixed $default = null): mixed` |
| `EventInterface::getName()` | `getName(): string` |
| `EventInterface::isStopped()` | `isStopped(): bool` |
| `AbstractEvent::__construct()`, `EventImmutable::__construct()` | `__construct(string $name, array $arguments = [])` |
| `AbstractEvent::hasArgument()` | `hasArgument(string\|int $name): bool` |
| `AbstractEvent::getArguments()` | `getArguments(): array` |
| `AbstractEvent::count()` | `count(): int` |
| `AbstractEvent::__serialize()` / `__unserialize()` | `__serialize(): array` / `__unserialize(array $data): void` |
| `AbstractEvent::offsetExists()` / `offsetGet()` | `offsetExists(mixed $name): bool` / `offsetGet(mixed $name): mixed` |
| `Event::addArgument()` / `setArgument()` | `(string\|int $name, mixed $value): static` |
| `Event::removeArgument()` | `removeArgument(string\|int $name): mixed` |
| `Event::clearArguments()` | `clearArguments(): array` |
| `Event`/`EventImmutable::offsetSet()` / `offsetUnset()` | `offsetSet(mixed $name, mixed $value): void` / `offsetUnset(mixed $name): void` |
| `DispatcherInterface::clearListeners()` | `clearListeners(?string $event = null): static` |
| `DispatcherInterface::countListeners()` | `countListeners(string $event): int` |
| `DispatcherInterface::getListeners()` | `getListeners(?string $event = null): array` |
| `DispatcherInterface::hasListener()` | `hasListener(callable $callback, ?string $eventName = null): bool` |
| `Dispatcher::getListenerPriority()` | `getListenerPriority(string $eventName, callable $callback): ?int` |
| `ListenersPriorityQueue` (internal) | `getPriority(): mixed`, `getIterator(): ArrayIterator`, `count(): int` |

The event name must now be a string (it is passed to `dispatch(string $name, ...)` anyway).
`AbstractEvent::$name`, `$arguments` and `$stopped` and `Dispatcher::$listeners` are typed
properties, so subclasses that redeclare them must use the same types.
`Dispatcher::getListenerPriority()` now returns `null` explicitly when the event is unknown
(previously the method ended without a return statement).
