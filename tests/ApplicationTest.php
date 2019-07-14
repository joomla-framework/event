<?php
/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Tests;

use Joomla\Console\Application;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Console\Command\HelpCommand;
use Joomla\Console\Command\ListCommand;
use Joomla\Console\ConsoleEvents;
use Joomla\Console\Event\ApplicationErrorEvent;
use Joomla\Console\Event\BeforeCommandExecuteEvent;
use Joomla\Console\Event\CommandErrorEvent;
use Joomla\Console\Exception\NamespaceNotFoundException;
use Joomla\Console\Loader\ContainerLoader;
use Joomla\Console\Tests\Fixtures\Command\AliasedCommand;
use Joomla\Console\Tests\Fixtures\Command\AnonymousCommand;
use Joomla\Console\Tests\Fixtures\Command\DisabledCommand;
use Joomla\Console\Tests\Fixtures\Command\NamespacedCommand;
use Joomla\Console\Tests\Fixtures\Command\SkipConfigurationCommand;
use Joomla\Console\Tests\Fixtures\Command\TopNamespacedCommand;
use Joomla\Event\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test class for \Joomla\Console\Application
 */
class ApplicationTest extends TestCase
{
	/**
	 * @var  Application
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp(): void
	{
		$this->object = new Application;
	}

	public function testTheApplicationIsExecuted()
	{
		$output = new BufferedOutput;

		$app = new Application(null, $output);
		$app->setAutoExit(false);
		$app->execute();

		$this->assertNotEmpty($output->fetch());
	}

	public function testSetGetName()
	{
		$this->object->setName('Console Application');
		$this->assertSame('Console Application', $this->object->getName());
	}

	public function testSetGetVersion()
	{
		$this->object->setVersion('1.0.0');
		$this->assertSame('1.0.0', $this->object->getVersion());
	}

	/**
	 * Data provider for testGetLongVersion
	 *
	 * @return  \Generator
	 */
	public function dataGetLongVersion(): \Generator
	{
		// Args: App Name, App Version, Expected Return
		yield 'Empty name and version' => ['', '', 'Joomla Console Application'];
		yield 'Name without version' => ['Console Application', '', 'Console Application'];
		yield 'Version without name' => ['', '1.0.0', 'Joomla Console Application <info>1.0.0</info>'];
		yield 'Version with name' => ['Console Application', '1.0.0', 'Console Application <info>1.0.0</info>'];
	}

	/**
	 * @param   string  $name      Application name
	 * @param   string  $version   Application version
	 * @param   string  $expected  Expected return
	 *
	 * @dataProvider  dataGetLongVersion
	 */
	public function testGetLongVersion(string $name, string $version, string $expected)
	{
		$this->object->setName($name);
		$this->object->setVersion($version);
		$this->assertSame($expected, $this->object->getLongVersion());
	}

	public function testGetAllCommands()
	{
		$commands = $this->object->getAllCommands();
		$this->assertInstanceOf(HelpCommand::class, $commands['help']);
		$this->assertInstanceOf(ListCommand::class, $commands['list']);

		$this->object->addCommand(new NamespacedCommand);
		$commands = $this->object->getAllCommands('test');
		$this->assertCount(1, $commands);
	}

	public function testGetAllCommandsWithCommandLoader()
	{
		$commands = $this->object->getAllCommands();
		$this->assertInstanceOf(HelpCommand::class, $commands['help']);
		$this->assertInstanceOf(ListCommand::class, $commands['list']);

		$this->object->addCommand(new NamespacedCommand);
		$commands = $this->object->getAllCommands('test');
		$this->assertCount(1, $commands);

		$container = $this->buildStubContainer();
		$container->set(
			'command.aliased',
			function (ContainerInterface $container)
			{
				return new AliasedCommand;
			}
		);

		$loader = new ContainerLoader($container, ['test:aliased' => 'command.aliased']);
		$this->object->setCommandLoader($loader);

		$commands = $this->object->getAllCommands('test');
		$this->assertCount(2, $commands);
	}

	public function testAddHasCommand()
	{
		$this->object->addCommand(new NamespacedCommand);
		$this->assertTrue($this->object->hasCommand('test:namespaced'));

		$this->object->addCommand(new DisabledCommand);
		$this->assertFalse($this->object->hasCommand('test:disabled'));
	}

	public function testAddCommandWithBrokenConstructor()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Command class "Joomla\Console\Tests\Fixtures\Command\SkipConfigurationCommand" is not correctly initialised.');

		$this->object->addCommand(new SkipConfigurationCommand);
	}

	public function testAddCommandWithNoName()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The command class "Joomla\Console\Tests\Fixtures\Command\AnonymousCommand" does not have a name.');

		$this->object->addCommand(new AnonymousCommand);
	}

	public function testHasGetCommand()
	{
		$this->assertTrue($this->object->hasCommand('list'));
		$this->assertFalse($this->object->hasCommand('non-existing-command'));

		$command = new NamespacedCommand;
		$this->object->addCommand($command);
		$this->assertTrue($this->object->hasCommand('test:namespaced'));
		$this->assertSame($command, $this->object->getCommand('test:namespaced'));

		// Simulates passing the --help option
		$r = new \ReflectionObject($this->object);
		$p = $r->getProperty('wantsHelp');
		$p->setAccessible(true);
		$p->setValue($this->object, true);

		/** @var HelpCommand $helpCommand */
		$helpCommand = $this->object->getCommand('test:namespaced');
		$this->assertInstanceOf(HelpCommand::class, $helpCommand);
		$this->assertAttributeSame(
			$command,
			'command',
			$helpCommand,
			'The getCommand method should inject the command help is being requested for'
		);
	}

	public function testHasGetCommandWithCommandLoader()
	{
		$this->assertTrue($this->object->hasCommand('list'));
		$this->assertFalse($this->object->hasCommand('non-existing-command'));

		$container = $this->buildStubContainer();
		$container->set(
			'command.namespaced',
			function (ContainerInterface $container)
			{
				return new NamespacedCommand;
			}
		);

		$loader = new ContainerLoader($container, ['test:namespaced' => 'command.namespaced']);
		$this->object->setCommandLoader($loader);

		$this->assertTrue($this->object->hasCommand('test:namespaced'));
		$this->assertInstanceOf(NamespacedCommand::class, $this->object->getCommand('test:namespaced'));
	}

	public function testGetCommandForUnknownCommand()
	{
		$this->expectException(CommandNotFoundException::class);
		$this->expectExceptionMessage('The command "test" does not exist.');

		$this->object->getCommand('test');
	}

	public function testGetNamespaces()
	{
		$this->object->addCommand(new NamespacedCommand);
		$this->object->addCommand(new AliasedCommand);

		$this->assertEquals(['test'], $this->object->getNamespaces());
	}

	public function testFindNamespace()
	{
		$this->object->addCommand(new NamespacedCommand);
		$this->assertEquals('test', $this->object->findNamespace('test'));
		$this->assertEquals(
			'test',
			$this->object->findNamespace('t'),
			'If an abbreviated namespace is given and is not ambiguous, the full namespace is returned'
		);

		$this->object->addCommand(new AliasedCommand);
		$this->assertEquals('test', $this->object->findNamespace('test'));
	}

	public function testFindAmbiguousNamespace()
	{
		$this->expectException(NamespaceNotFoundException::class);
		$this->expectExceptionMessage('The namespace "t" is ambiguous.');

		$this->object->addCommand(new NamespacedCommand);
		$this->object->addCommand(new TopNamespacedCommand());

		$this->object->findNamespace('t');
	}

	public function testFindUnknownNamespace()
	{
		$this->expectException(NamespaceNotFoundException::class);
		$this->expectExceptionMessage('There are no commands defined in the "test" namespace.');

		$this->object->findNamespace('test');
	}

	public function testNoOutputWhenHelpRequestedWithQuietFlag()
	{
		$input = new ArrayInput(
			[
				'-h' => true,
				'-q' => true,
			]
		);
		$output = new BufferedOutput;

		$app = new Application($input, $output);
		$app->setAutoExit(false);
		$app->execute();

		$this->assertEmpty($output->fetch());
	}

	public function testHandlingThrowables()
	{
		$input = new ArrayInput(
			[
				'command' => 'foo',
			]
		);
		$output = new BufferedOutput;

		$app = new Application($input, $output);
		$app->setCatchThrowables(true);
		$app->setAutoExit(false);
		$app->execute();

		$screenOutput = $output->fetch();
		$this->assertNotEmpty($screenOutput);
		$this->assertStringContainsString('Command "foo" is not defined.', $screenOutput);

		$app->setCatchThrowables(false);

		try
		{
			$app->execute();
			$this->fail('The Throwable from the application should have been caught');
		}
		catch (\Throwable $exception)
		{
			$this->assertInstanceOf(CommandNotFoundException::class, $exception);
			$this->assertEquals('The command "foo" does not exist.', $exception->getMessage());
		}
	}

	public function testAppIsClosedWhenAutoExitIsEnabled()
	{
		$output = new NullOutput;

		$app = $this->buildMockClosingApplication(null, $output);

		$app->setAutoExit(true);
		$app->execute();

		$this->assertTrue($app->wasClosed());
	}

	public function testExitCodeIsSetByEventListener()
	{
		$dispatcher = new Dispatcher;
		$dispatcher->addListener(
			ConsoleEvents::APPLICATION_ERROR,
			function (ApplicationErrorEvent $event)
			{
				$event->setExitCode(119);
			}
		);

		$input = new ArrayInput(
			[
				'command' => 'foo',
			]
		);
		$output = new NullOutput;

		$app = $this->buildMockClosingApplication($input, $output);

		$app->setDispatcher($dispatcher);
		$app->setAutoExit(true);
		$app->execute();

		$this->assertSame(119, $app->getExitCode());
	}

	public function testCommandNotFoundHasSuccessExitIfEventListenerSpecifiesSo()
	{
		$dispatcher = new Dispatcher;
		$dispatcher->addListener(
			ConsoleEvents::COMMAND_ERROR,
			function (CommandErrorEvent $event)
			{
				$event->setExitCode(0);
			}
		);

		$input = new ArrayInput(
			[
				'command' => 'foo',
			]
		);
		$output = new NullOutput;

		$app = $this->buildMockClosingApplication($input, $output);

		$app->setDispatcher($dispatcher);
		$app->setAutoExit(true);
		$app->execute();

		$this->assertSame(0, $app->getExitCode());
	}

	public function testCommandErrorHasExitCodeOneIfExceptionHasCodeZero()
	{
		$command = new class extends AbstractCommand
		{
			protected static $defaultName = 'exception';

			protected function doExecute(InputInterface $input, OutputInterface $output): int
			{
				throw new \Exception('Testing', 0);
			}
		};

		$input = new ArrayInput(
			[
				'command' => 'exception',
			]
		);
		$output = new NullOutput;

		$app = $this->buildMockClosingApplication($input, $output);

		$app->addCommand($command);
		$app->setAutoExit(true);
		$app->execute();

		$this->assertSame(1, $app->getExitCode());
	}

	public function testCommandIsNotExecutedIfEventSkipsCommand()
	{
		$command = new class extends AbstractCommand
		{
			protected static $defaultName = 'no-run';

			private $executed = false;

			protected function doExecute(InputInterface $input, OutputInterface $output): int
			{
				$this->executed = true;

				return 0;
			}

			public function wasExecuted(): bool
			{
				return $this->executed;
			}
		};

		$dispatcher = new Dispatcher;
		$dispatcher->addListener(
			ConsoleEvents::BEFORE_COMMAND_EXECUTE,
			function (BeforeCommandExecuteEvent $event) use ($command)
			{
				$this->assertSame($command, $event->getCommand());
				$event->disableCommand();
			}
		);

		$input = new ArrayInput(
			[
				'command' => 'no-run',
			]
		);
		$output = new NullOutput;

		$app = $this->buildMockClosingApplication($input, $output);

		$app->setDispatcher($dispatcher);
		$app->addCommand($command);
		$app->setAutoExit(true);
		$app->execute();

		$this->assertFalse($command->wasExecuted());
		$this->assertSame(BeforeCommandExecuteEvent::RETURN_CODE_DISABLED, $app->getExitCode());
	}

	private function buildMockClosingApplication(InputInterface $input = null, OutputInterface $output = null): Application
	{
		return new class($input, $output) extends Application
		{
			private $exitCode = 0;
			private $wasClosed = false;

			public function close($code = 0)
			{
				$this->exitCode  = $code;
				$this->wasClosed = true;
			}

			public function getExitCode(): int
			{
				return $this->exitCode;
			}

			public function wasClosed(): bool
			{
				return $this->wasClosed;
			}
		};
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
