<?php

namespace Tests\Unit\Commands;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use LaravelMCP\MCP\Commands\MCPServerCommand;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use LaravelMCP\MCP\Transport\TransportFactory;
use PHPUnit\Framework\MockObject\MockObject;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class MCPServerCommandTest extends TestCase
{
    /** @var TransportInterface&MockObject */
    protected $transport;

    protected MCPServerCommand $command;

    /** @var TransportFactory&MockObject */
    protected $factory;

    /** @var LoopInterface|null */
    protected $originalLoop;

    /** @var Application|null */
    protected $app;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\React\EventLoop\LoopInterface */
    protected $loop;

    /** @var MCPServerInterface&MockObject */
    protected $server;

    protected function setUp(): void
    {
        parent::setUp();

        // Store the original loop
        $this->originalLoop = Loop::get();

        // Create mock transport and factory
        $this->transport = $this->createMock(TransportInterface::class);
        $this->factory = $this->createMock(TransportFactory::class);
        $this->factory->method('create')->willReturn($this->transport);

        // Create mock server and loop
        $this->server = $this->createMock(MCPServerInterface::class);
        $this->server->method('setTransport')->willReturnSelf();
        $this->server->method('start')->willReturnSelf();
        $this->loop = $this->createMock(LoopInterface::class);

        // Create the command
        $this->command = new MCPServerCommand(
            $this->server,
            $this->factory,
            $this->loop
        );

        // Set up the application and Artisan facade
        $this->app = $this->createApplication();
        $this->app->singleton('artisan', function ($app) {
            return new \Illuminate\Console\Application($app, $app['events'], $app->version());
        });
        Artisan::setFacadeApplication($this->app);
        $this->command->setLaravel($this->app);

        // Set up default input/output
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore the original loop
        if ($this->originalLoop !== null) {
            Loop::set($this->originalLoop);
        }

        // Clean up created directories
        $configPath = dirname(__DIR__, 3) . '/config';
        if (is_dir($configPath)) {
            rmdir($configPath);
        }
    }

    protected function createApplication(): Application
    {
        $app = new Application(
            $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__, 3)
        );

        $app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            \Illuminate\Foundation\Console\Kernel::class
        );

        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Illuminate\Foundation\Exceptions\Handler::class
        );

        // Register the events service
        $app->singleton('events', function ($app) {
            return new \Illuminate\Events\Dispatcher($app);
        });

        return $app;
    }

    public function testItRegistersCommandInConsole(): void
    {
        // Ensure app is not null and properly configured
        if ($this->app === null) {
            $basePath = dirname(__DIR__, 3);
            $this->app = new Application($basePath);
            $this->app->setBasePath($basePath);

            // Register required services
            $this->app->singleton('config', function () {
                return new \Illuminate\Config\Repository([
                    'app' => [
                        'name' => 'Laravel MCP SDK Test',
                        'commands' => [],
                        'providers' => [],
                    ],
                    'view' => [
                        'paths' => [],
                        'compiled' => sys_get_temp_dir(),
                    ],
                ]);
            });

            $this->app->singleton('events', function ($app) {
                return new \Illuminate\Events\Dispatcher($app);
            });
        }

        // Create and register the console kernel with minimal configuration
        $this->app->singleton(
            \Illuminate\Contracts\Console\Kernel::class,
            function ($app) {
                return new class ($app, $app['events']) extends \Illuminate\Foundation\Console\Kernel {
                    protected $commands = [];

                    public function bootstrap()
                    {
                        // Skip bootstrapping to avoid filesystem operations
                    }
                };
            }
        );

        // Create Artisan console application
        $artisan = new \Illuminate\Console\Application($this->app, $this->app['events'], $this->app->version());
        $this->app->instance('artisan', $artisan);

        // Register the command directly
        $artisan->add($this->command);

        // Get all registered commands
        $commands = $artisan->all();
        $this->assertArrayHasKey('mcp:serve', $commands, 'The mcp:serve command is not registered');

        // Verify the command instance is properly configured
        $command = $commands['mcp:serve'];
        $this->assertInstanceOf(MCPServerCommand::class, $command);
    }

    public function testCommandOptions(): void
    {
        $definition = $this->command->getDefinition();

        $this->assertArrayHasKey('port', $definition->getOptions());
        $this->assertArrayHasKey('host', $definition->getOptions());
        $this->assertArrayHasKey('transport', $definition->getOptions());
    }

    public function testCommandDefaultOptions(): void
    {
        $definition = $this->command->getDefinition();

        $this->assertEquals('8080', $definition->getOption('port')->getDefault());
        $this->assertEquals('127.0.0.1', $definition->getOption('host')->getDefault());
        $this->assertEquals('http', $definition->getOption('transport')->getDefault());
    }

    public function testHandleWithDefaultOptions(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('http'),
                $this->server,
                $this->loop,
                $this->equalTo([
                    'host' => '127.0.0.1',
                    'port' => 8080,
                ])
            )
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport);

        $this->server->expects($this->once())
            ->method('start');

        $this->loop->expects($this->once())
            ->method('run');

        $result = $this->command->handle();
        $this->assertEquals(0, $result);
    }

    public function testHandleWithCustomOptions(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([
            '--transport' => 'tcp',
            '--host' => 'localhost',
            '--port' => '3000',
        ], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('tcp'),
                $this->server,
                $this->loop,
                $this->equalTo([
                    'host' => 'localhost',
                    'port' => 3000,
                ])
            )
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport);

        $this->server->expects($this->once())
            ->method('start');

        $this->loop->expects($this->once())
            ->method('run');

        $result = $this->command->handle();
        $this->assertEquals(0, $result);
    }

    public function testHandleWithInvalidPortString(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([
            '--port' => 'invalid',
        ], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->never())
            ->method('create');

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    public function testHandleWithTransportCreationError(): void
    {
        $this->factory->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Transport creation failed'));

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    public function testHandleWithTransportStartError(): void
    {
        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport)
            ->willReturnSelf();

        $this->server->expects($this->once())
            ->method('start')
            ->willThrowException(new \Exception('Transport start failed'));

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    public function testSignalHandlersWithoutPcntl(): void
    {
        if (extension_loaded('pcntl')) {
            $this->markTestSkipped('This test requires pcntl to be disabled.');
        }

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport)
            ->willReturnSelf();

        $this->server->expects($this->once())
            ->method('start')
            ->willReturnSelf();

        $result = $this->command->handle();
        $this->assertEquals(0, $result);
    }

    /**
     * @requires extension pcntl
     */
    public function testSignalHandlersWithPcntl(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('This test requires PCNTL extension');
        }

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport)
            ->willReturnSelf();

        $this->server->expects($this->once())
            ->method('start')
            ->willReturnSelf();
        $this->server->expects($this->once())
            ->method('stop');

        $result = $this->command->handle();

        $this->sendSignal(SIGTERM);

        $this->assertEquals(0, $result);
    }

    public function testSignalHandlersWithSigint(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('This test requires PCNTL extension');
        }

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport)
            ->willReturnSelf();

        $this->server->expects($this->once())
            ->method('start')
            ->willReturnSelf();
        $this->server->expects($this->once())
            ->method('stop');

        $result = $this->command->handle();

        $this->sendSignal(SIGINT);

        $this->assertEquals(0, $result);
    }

    public function testHandleWithNonStringTransportOption(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([
            '--transport' => 123,
        ], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->never())
            ->method('create');

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    public function testHandleWithNonStringHostOption(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([
            '--host' => 123,
        ], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->never())
            ->method('create');

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    public function testHandleWithNonStringPortOption(): void
    {
        $definition = $this->command->getDefinition();
        $input = new ArrayInput([
            '--port' => [],
        ], $definition);
        $output = new OutputStyle($input, new BufferedOutput());
        $this->command->setInput($input);
        $this->command->setOutput($output);

        $this->factory->expects($this->never())
            ->method('create');

        $result = $this->command->handle();
        $this->assertEquals(1, $result);
    }

    /**
     * @requires extension pcntl
     */
    public function testSignalHandlersWithPcntlAndNoTransport(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('This test requires PCNTL extension');
        }

        $this->factory->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Transport creation failed'));

        $result = $this->command->handle();

        $this->sendSignal(SIGTERM);

        $this->assertEquals(1, $result);
    }

    /**
     * Helper method to safely send a signal to the current process
     */
    private function sendSignal(int $signal): void
    {
        if (function_exists('posix_kill')) {
            $pid = getmypid();
            if ($pid !== false) {
                posix_kill($pid, $signal);
                if (function_exists('pcntl_signal_dispatch')) {
                    pcntl_signal_dispatch();
                }
            }
        }
    }

    public function testSignalHandlersWithBothSignals(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('This test requires PCNTL extension');
        }

        $this->factory->expects($this->once())
            ->method('create')
            ->willReturn($this->transport);

        $this->server->expects($this->once())
            ->method('setTransport')
            ->with($this->transport)
            ->willReturnSelf();

        $this->server->expects($this->once())
            ->method('start')
            ->willReturnSelf();
        $this->server->expects($this->exactly(2))
            ->method('stop');

        $result = $this->command->handle();

        $this->sendSignal(SIGINT);
        $this->sendSignal(SIGTERM);

        $this->assertEquals(0, $result);
    }
}
