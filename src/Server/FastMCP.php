<?php

namespace LaravelMCP\MCP\Server;

use Closure;
use LaravelMCP\MCP\Contracts\MCPServerInterface;

/**
 * A fast implementation of the MCP server interface.
 *
 * This class provides a lightweight and efficient implementation of the MCP server
 * with support for tools, resources, prompts, and lifecycle management.
 *
 * @package LaravelMCP\MCP\Server
 */
class FastMCP extends MCPServer
{
    /** @var MCPServerInterface The underlying MCP server instance */
    protected MCPServerInterface $server;

    /** @var array List of server dependencies */
    protected array $dependencies = [];

    /** @var Closure|null Server lifecycle handler */
    protected ?Closure $lifespan = null;

    /**
     * Create a new FastMCP instance.
     *
     * @param MCPServerInterface|null $server Optional server instance to use
     */
    public function __construct(?MCPServerInterface $server = null)
    {
        parent::__construct();
        $this->server = $server ?? $this;
    }

    /**
     * Register a tool with the server.
     *
     * @param string $name Tool name
     * @param array $parameters Tool parameters
     * @return Closure Registration handler
     */
    public function tool(string $name, array $parameters = []): Closure
    {
        return function (callable $handler) use ($name, $parameters) {
            $this->server->registerTool($name, $handler, $parameters);
        };
    }

    /**
     * Register a resource with the server.
     *
     * @param string $uri Resource URI
     * @return Closure Registration handler
     */
    public function resource(string $uri): Closure
    {
        return function (callable $handler) use ($uri) {
            $this->server->registerResource($uri, $handler);
        };
    }

    /**
     * Register a prompt with the server.
     *
     * @param string $name Prompt name
     * @param array $arguments Prompt arguments
     * @return Closure Registration handler
     */
    public function prompt(string $name, array $arguments = []): Closure
    {
        return function (callable $handler) use ($name, $arguments) {
            $this->server->registerPrompt($name, $handler, $arguments);
        };
    }

    /**
     * Set the server lifecycle handler.
     *
     * @param Closure $handler Lifecycle handler function
     */
    public function lifespan(Closure $handler): void
    {
        $this->lifespan = $handler;
    }

    /**
     * Get the underlying server instance.
     *
     * @return MCPServerInterface
     */
    public function getServer(): MCPServerInterface
    {
        return $this->server;
    }

    /**
     * Get the list of server dependencies.
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Get the server lifecycle handler.
     *
     * @return Closure|null
     */
    public function getLifespan(): ?Closure
    {
        return $this->lifespan;
    }

    /**
     * Handle completion requests.
     *
     * @param array $arguments Completion arguments
     * @param array $messages Message history
     * @return array Completion response
     */
    public function handleCompletion(array $arguments, array $messages): array
    {
        // Implement fast completion handling
        return [];
    }
}
