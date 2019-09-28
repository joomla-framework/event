<?php
/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Event\Tests;

use Joomla\Event\EventInterface;
use Joomla\Event\LazyServiceEventListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Tests for the LazyServiceEventListener class.
 */
class LazyServiceEventListenerTest extends TestCase
{
	/**
	 * @doesNotPerformAssertions
	 */
	public function testListenerCanBeInstantiatedWithoutMethod()
	{
		$serviceId = 'lazy.object';

		$container = $this->buildStubContainer();
		$container->set(
			$serviceId,
			function (ContainerInterface $container)
			{
				return new \stdClass;
			}
		);

		new LazyServiceEventListener($container, $serviceId);
	}

	public function testListenerCannotBeInstantiatedWithoutAServiceId()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			sprintf(
				'The $serviceId parameter cannot be empty in %s',
				LazyServiceEventListener::class
			)
		);

		$container = $this->buildStubContainer();
		$container->set(
			'lazy.object',
			function (ContainerInterface $container)
			{
				return new \stdClass;
			}
		);

		new LazyServiceEventListener($container, '');
	}

	public function testListenerTriggersAnInvokableClass()
	{
		$serviceId = 'lazy.object';

		$service = new class
		{
			private $triggered = false;

			public function __invoke(): void
			{
				$this->triggered = true;
			}

			public function isTriggered(): bool
			{
				return $this->triggered;
			}
		};

		$container = $this->buildStubContainer();
		$container->set(
			$serviceId,
			function () use ($service)
			{
				return $service;
			}
		);

		$event = $this->createMock(EventInterface::class);

		$listener = new LazyServiceEventListener($container, $serviceId);
		$listener($event);

		$this->assertTrue($service->isTriggered());
	}

	public function testListenerTriggersAMethodOnAClass()
	{
		$serviceId = 'lazy.object';

		$service = new class
		{
			private $triggered = false;

			public function isTriggered(): bool
			{
				return $this->triggered;
			}

			public function trigger(): void
			{
				$this->triggered = true;
			}
		};

		$container = $this->buildStubContainer();
		$container->set(
			$serviceId,
			function () use ($service)
			{
				return $service;
			}
		);

		$event = $this->createMock(EventInterface::class);

		$listener = new LazyServiceEventListener($container, $serviceId, 'trigger');
		$listener($event);

		$this->assertTrue($service->isTriggered());
	}

	public function testListenerCannotTriggerAnUnknownService()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('The "lazy.object" service has not been registered to the service container');

		$container = $this->buildStubContainer();

		$event = $this->createMock(EventInterface::class);

		$listener = new LazyServiceEventListener($container, 'lazy.object');
		$listener($event);
	}

	public function testListenerCannotTriggerAMethodWhenMethodNameNotGivenAndClassNotInvokable()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage(
			sprintf(
				'The $method argument is required when creating a "%s" to call a method from the "lazy.object" service.',
				LazyServiceEventListener::class
			)
		);

		$serviceId = 'lazy.object';

		$container = $this->buildStubContainer();
		$container->set(
			$serviceId,
			function ()
			{
				return new class
				{
					private $triggered = false;

					public function trigger(): void
					{
						$this->triggered = true;
					}
				};
			}
		);

		$event = $this->createMock(EventInterface::class);

		$listener = new LazyServiceEventListener($container, $serviceId);
		$listener($event);
	}

	public function testListenerCannotTriggerAMethodWhenTheGivenMethodNameDoesNotExist()
	{
		$service = new class
		{
			private $triggered = false;

			public function trigger(): void
			{
				$this->triggered = true;
			}
		};

		$serviceId = 'lazy.object';

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage(
			sprintf(
				'The "doIt" method does not exist on "%s" (from service "%s")',
				\get_class($service),
				$serviceId
			)
		);

		$container = $this->buildStubContainer();
		$container->set(
			$serviceId,
			function () use ($service)
			{
				return $service;
			}
		);

		$event = $this->createMock(EventInterface::class);

		$listener = new LazyServiceEventListener($container, $serviceId, 'doIt');
		$listener($event);
	}

	private function buildStubContainer(): ContainerInterface
	{
		return new class implements ContainerInterface
		{
			private $services = [];

			public function get($id)
			{
				if (!$this->has($id))
				{
					throw new class extends \InvalidArgumentException implements NotFoundExceptionInterface {};
				}

				return $this->services[$id]($this);
			}

			public function has($id)
			{
				return isset($this->services[$id]);
			}

			public function set($id, $value)
			{
				if (!is_callable($value))
				{
					$value = function () use ($value) {
						return $value;
					};
				}

				$this->services[$id] = $value;
			}
		};
	}
}
