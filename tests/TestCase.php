<?php

namespace Tests;

use Illuminate\Foundation\Application;
use LaravelMCP\MCP\MCPServiceProvider;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var Application|null */
    protected $app;

    /**
     * @param Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MCPServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];
        // Setup default database to use sqlite :memory:
        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a new application instance if not exists
        if ($this->app === null) {
            $this->app = new Application();
            $this->app->singleton('config', function () {
                return new \Illuminate\Config\Repository();
            });
        }

        if (class_exists('Mockery')) {
            Mockery::close();
        }

        if ($this->app !== null) {
            /** @var Application&Mockery\LegacyMockInterface $mockedApp */
            $mockedApp = Mockery::mock($this->app)->makePartial();
            $this->app = $mockedApp;
        }
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            $this->app = null;
        }
        parent::tearDown();

        if (class_exists('Mockery')) {
            Mockery::close();
        }
    }
}
