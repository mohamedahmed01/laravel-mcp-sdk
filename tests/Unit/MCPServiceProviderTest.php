<?php

namespace Tests\Unit;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use LaravelMCP\MCP\Commands\MCPServerCommand;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\MCPServiceProvider;
use LaravelMCP\MCP\Server\FastMCP;
use LaravelMCP\MCP\Transport\TransportFactory;
use Mockery;
use Tests\TestCase;

class MCPServiceProviderTest extends TestCase
{
    /** @var Application */
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure app is not null and properly configured
        if ($this->app === null) {
            $basePath = dirname(__DIR__, 2);
            $this->app = new Application($basePath);
            $this->app->setBasePath($basePath);

            // Create minimal directory structure
            $configPath = $basePath . '/config';
            if (! is_dir($configPath)) {
                mkdir($configPath, 0777, true);
            }
        }

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

        $this->app->singleton('files', function () {
            return new \Illuminate\Filesystem\Filesystem();
        });

        // Register the service provider
        $provider = new MCPServiceProvider($this->app);
        $provider->register();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up created directories
        $configPath = dirname(__DIR__, 2) . '/config';
        if (is_dir($configPath)) {
            rmdir($configPath);
        }
    }

    public function testItRegistersServices(): void
    {
        // Ensure app is not null
        $this->assertNotNull($this->app);

        // Test that services are properly bound and resolved
        $server = $this->app->make(MCPServerInterface::class);
        $this->assertInstanceOf(FastMCP::class, $server);

        $factory = $this->app->make(TransportFactory::class);
        $this->assertInstanceOf(TransportFactory::class, $factory);

        $command = $this->app->make(MCPServerCommand::class);
        $this->assertInstanceOf(MCPServerCommand::class, $command);

        // Test that services are registered as singletons
        $secondServer = $this->app->make(MCPServerInterface::class);
        $this->assertSame($server, $secondServer, 'MCPServerInterface is not registered as singleton');

        $secondFactory = $this->app->make(TransportFactory::class);
        $this->assertSame($factory, $secondFactory, 'TransportFactory is not registered as singleton');

        $secondCommand = $this->app->make(MCPServerCommand::class);
        $this->assertSame($command, $secondCommand, 'MCPServerCommand is not registered as singleton');
    }

    public function testItRegistersCommandInConsole(): void
    {
        // Ensure app is not null
        $this->assertNotNull($this->app);

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

        // Boot the service provider to register commands
        $provider = new MCPServiceProvider($this->app);
        $provider->boot();

        /** @var Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);
        $this->assertNotNull($kernel);

        // Get all registered commands
        $commands = $kernel->all();
        $this->assertArrayHasKey('mcp:serve', $commands, 'The mcp:serve command is not registered');

        // Verify the command instance is properly configured
        $command = $commands['mcp:serve'];
        $this->assertInstanceOf(MCPServerCommand::class, $command);
    }

    public function testItPublishesConfigInConsole(): void
    {
        // Ensure app is not null
        $this->assertNotNull($this->app);

        // Get the service provider instance
        $provider = new MCPServiceProvider($this->app);

        // Get the published paths
        $publishedPaths = $provider->pathsToPublish();
        $this->assertNotNull($publishedPaths, 'No paths to publish found');
        $this->assertIsArray($publishedPaths);

        // Verify that the config file is included in the published paths
        $configFile = null;
        $configGroup = null;
        foreach ($publishedPaths as $sourcePath => $group) {
            if (is_string($sourcePath) && str_ends_with($sourcePath, 'config/mcp.php')) {
                $configFile = $sourcePath;
                $configGroup = $group;

                break;
            }
        }

        // Assert config file exists and has correct path
        $this->assertNotNull($configFile, 'MCP config file not found in published paths');
        $this->assertIsString($configFile);
        $this->assertStringEndsWith('config/mcp.php', $configFile, 'Config file path is incorrect');

        // Assert config file is published in the correct group
        $this->assertNotNull($configGroup, 'Config file has no publishing group');
        $this->assertIsString($configGroup);
    }

    public function testItSkipsBootingWhenNotInConsole(): void
    {
        // Ensure app is not null
        $this->assertNotNull($this->app);

        // Mock the application to return false for runningInConsole
        /** @var Application&Mockery\LegacyMockInterface $app */
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('runningInConsole')
            ->once()
            ->andReturn(false);

        // Create a new instance of the service provider
        $provider = new MCPServiceProvider($app);

        // The app should not receive any calls to register commands
        $app->shouldNotReceive('commands');

        // Boot the provider - this should not throw any errors and should not register commands
        $provider->boot();
    }
}
