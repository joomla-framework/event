# Updating from v3 to v4

Release 4.0.0 raises the PHP requirement. No method signature changed, and nothing was removed —
but the deprecations announced for 3.0 were rescheduled.

## At a glance

| | v3 (3.0.0) | v4 (4.0.0) |
|---|---|---|
| PHP | `^8.1.0` | `^8.3.0` |
| Public API | — | unchanged |
| Deprecation target | "removed in 3.0" | "removed in 5.0" |

## Minimum supported PHP version raised

All Framework packages now require **PHP 8.3** or newer.

## No API changes

`git diff 3.0.0 4.0.0 -- src/` touches three files and changes only docblocks and deprecation
messages. Every class and method behaves as before.

## The 2.0 deprecations are still here — now targeting 5.0

`Dispatcher` carries an event registry from the 1.x API that was deprecated in 2.0 and announced
for removal in 3.0. That removal did not happen. The annotations and runtime messages were
corrected in 4.0.0 to name the real target:

```php
// v3 runtime message
// "Joomla\Event\Dispatcher::setEvent() is deprecated and will be removed in 3.0."

// v4
// "Joomla\Event\Dispatcher::setEvent() is deprecated and will be removed in 5.0."
```

The affected methods are `setEvent()`, `addEvent()`, `hasEvent()`, `getEvent()`, `removeEvent()`,
`getEvents()`, `clearEvents()`, `countEvents()` and `triggerEvent()`, plus dispatching without
passing an event object:

```php
// Deprecated: the dispatcher builds a default Event for you
$dispatcher->dispatch('onSomething');

// Pass the event
$dispatcher->dispatch('onSomething', new Event('onSomething'));
```

Nothing breaks in 4.0, but the notices are now truthful about when it will. If you silenced them
because the announced version had passed, this is the point to address them instead.

Note that `getDefaultEvent()` — used when `dispatch()` is called without an event — reads from that
same deprecated registry, so there is currently no non-deprecated way to register a default event
object. Pass the event explicitly.

## Dependency changes

| Package | v3 (3.0.0) | v4 (4.0.0) |
|---|---|---|
| `php` | `^8.1.0` | `^8.3.0` |
| `symfony/deprecation-contracts` | `^2 \| ^3` | unchanged |

`joomla/console` and a `psr/container-implementation` remain optional, in `suggest`, for the
`debug:event-dispatcher` command and `LazyServiceEventListener`.
