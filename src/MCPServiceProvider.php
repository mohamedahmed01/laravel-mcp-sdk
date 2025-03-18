<?php

namespace LaravelMCP\MCP;

use Illuminate\Support\ServiceProvider;
use LaravelMCP\MCP\Commands\MCPServerCommand;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Server\FastMCP;
use LaravelMCP\MCP\Transport\TransportFactory;

/**
 * Service provider for the MCP system in Laravel.
 *
 * This provider is responsible for registering the MCP system's services,
 * commands, and bindings with the Laravel service container. It handles
 * the initialization and configuration of the MCP system within a Laravel
 * application.
 *
 * Services registered:
 * - MCPServerInterface -> FastMCP (singleton)
 * - TransportFactory (singleton)
 * - LoopInterface -> React EventLoop (singleton)
 * - MCPServerCommand (singleton)
 *
 * Configuration:
 * - Publishes config/mcp.php to the application's config directory
 * - Configuration can be customized through the published config file
 *
 * Commands:
 * - mcp:server: Starts the MCP server with the configured transport
 *
 * @package LaravelMCP\MCP
 */
class MCPServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method binds the MCP system's core services to the container:
     * 1. MCPServerInterface -> FastMCP implementation
     * 2. TransportFactory for creating transport instances
     * 3. React EventLoop for handling async operations
     * 4. MCPServerCommand for CLI interaction
     *
     * All services are registered as singletons to ensure consistent
     * state across the application.
     */
    public function register(): void
    {
        $this->app->singleton(MCPServerInterface::class, function ($app) {
            return new FastMCP();
        });

        $this->app->singleton(TransportFactory::class, function ($app) {
            return new TransportFactory();
        });

        $this->app->singleton(\React\EventLoop\LoopInterface::class, function ($app) {
            return \React\EventLoop\Loop::get();
        });

        $this->app->singleton(MCPServerCommand::class, function ($app) {
            return new MCPServerCommand(
                $app->make(MCPServerInterface::class),
                $app->make(TransportFactory::class),
                $app->make(\React\EventLoop\LoopInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been
     * registered. It performs the following tasks:
     * 1. Publishes the MCP configuration file to config/mcp.php
     * 2. Registers the mcp:server command for CLI usage
     *
     * These operations only run when the application is executed from
     * the command line (php artisan).
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/mcp.php' => config_path('mcp.php'),
            ], 'config');

            $this->commands([
                MCPServerCommand::class,
            ]);
        }
    }
}
