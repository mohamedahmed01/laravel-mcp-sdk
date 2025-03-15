<?php

namespace LaravelMCP\MCP\Server;

use LaravelMCP\MCP\Contracts\ToolInterface;

/**
 * Implementation of a tool in the MCP system.
 *
 * A tool represents a specific functionality that can be invoked by clients
 * through the MCP server. Each tool has a unique name, a handler function
 * that implements its logic, and optional configuration parameters.
 *
 * Tools can be used to:
 * - Execute system commands
 * - Manipulate files and resources
 * - Process data
 * - Integrate with external services
 *
 * @package LaravelMCP\MCP\Server
 */
class Tool implements ToolInterface
{
    /**
     * @var string The unique identifier of the tool
     */
    private string $name;

    /**
     * @var callable The function that implements the tool's logic
     */
    private $handler;

    /**
     * @var string|null Description of the tool's purpose and usage
     */
    private ?string $description;

    /**
     * @var array Configuration parameters for the tool
     */
    private array $parameters = [];

    /**
     * Create a new tool instance.
     *
     * @param string $name The unique identifier for the tool
     * @param callable $handler The function that implements the tool's logic
     * @param string|null $description Optional description of the tool's purpose
     */
    public function __construct(string $name, callable $handler, ?string $description = null)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Handle a request to execute this tool.
     *
     * Processes the provided arguments using the tool's handler function
     * and returns the result. The handler's return value is automatically
     * converted to an array if it isn't one already.
     *
     * @param array $arguments The arguments to pass to the handler
     * @return array The result of executing the tool
     */
    public function handle(array $arguments): array
    {
        $result = ($this->handler)($arguments);

        return is_array($result) ? $result : [$result];
    }
}
