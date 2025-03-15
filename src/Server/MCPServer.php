<?php

namespace LaravelMCP\MCP\Server;

use InvalidArgumentException;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Contracts\PromptInterface;
use LaravelMCP\MCP\Contracts\ResourceInterface;
use LaravelMCP\MCP\Contracts\ResourceTemplateInterface;
use LaravelMCP\MCP\Contracts\ToolInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use LaravelMCP\MCP\Root;
use LaravelMCP\MCP\Sampling\ModelPreferences;
use RuntimeException;

/**
 * The main MCP (Model-Controller-Prompt) server implementation.
 *
 * This class serves as the core of the MCP system, managing tools, resources,
 * prompts, and handling communication through the transport layer. It provides
 * a robust platform for building AI-powered applications with Laravel.
 *
 * The server supports:
 * - Tool registration and execution
 * - Resource management and templating
 * - Prompt handling and processing
 * - Progress tracking and logging
 * - Model preference configuration
 *
 * @package LaravelMCP\MCP\Server
 */
class MCPServer implements MCPServerInterface
{
    /**
     * @var TransportInterface|null The transport layer for communication
     */
    private ?TransportInterface $transport = null;

    /**
     * @var array<string, ToolInterface> Map of registered tools
     */
    private array $tools = [];

    /**
     * @var array<string, ResourceInterface> Map of registered resources
     */
    private array $resources = [];

    /**
     * @var array<string, ResourceTemplateInterface> Map of registered resource templates
     */
    private array $resourceTemplates = [];

    /**
     * @var array<string, PromptInterface> Map of registered prompts
     */
    private array $prompts = [];

    /**
     * @var array<string, Root> Map of registered roots
     */
    private array $roots = [];

    /**
     * @var ModelPreferences Current model preferences for sampling
     */
    private ModelPreferences $preferences;

    /**
     * Create a new MCP server instance.
     */
    public function __construct()
    {
        $this->preferences = new ModelPreferences();
    }

    /**
     * Set the transport layer for the server.
     *
     * @param TransportInterface $transport The transport to use
     */
    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * Get the current transport layer.
     *
     * @return TransportInterface|null The current transport or null if not set
     */
    public function getTransport(): ?TransportInterface
    {
        return $this->transport;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport must be set before initializing server');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function registerTool(string $name, callable $handler, array $parameters = []): void
    {
        $tool = new Tool($name, $handler);
        $tool->setParameters($parameters);
        $this->tools[$name] = $tool;
    }

    /**
     * {@inheritdoc}
     */
    public function registerResource(string $uri, callable $handler): void
    {
        $resource = new Resource($uri, $handler);
        $this->resources[$uri] = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapabilities(): array
    {
        return [
            'logging' => true,
            'progress' => true,
            'completion' => true,
            'tools' => ! empty($this->tools),
            'resources' => ! empty($this->resources),
            'prompts' => ! empty($this->prompts),
            'roots' => ! empty($this->roots),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleToolCall(string $name, array $arguments): mixed
    {
        if (! isset($this->tools[$name])) {
            throw new RuntimeException("Unknown tool: {$name}");
        }

        $result = $this->tools[$name]->handle($arguments);

        return is_array($result) ? $result : ['result' => $result];
    }

    /**
     * {@inheritdoc}
     */
    public function handleResourceRequest(string $uri): array
    {
        if (! isset($this->resources[$uri])) {
            throw new RuntimeException("Unknown resource: {$uri}");
        }

        $resource = $this->resources[$uri];
        $result = $resource->handle();

        if (! is_array($result)) {
            $result = ['content' => $result];
        }

        if ($resource->getMimeType()) {
            $result['mime_type'] = $resource->getMimeType();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function handlePromptRequest(string $name, array $arguments): mixed
    {
        if (! isset($this->prompts[$name])) {
            throw new RuntimeException("Unknown prompt: {$name}");
        }

        $prompt = $this->prompts[$name];

        return array_merge($prompt->getArguments(), $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceTemplates(): array
    {
        return $this->resourceTemplates;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrompts(): array
    {
        return $this->prompts;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoots(): array
    {
        return $this->roots;
    }

    /**
     * Add a tool to the server.
     *
     * @param string $name The unique identifier for the tool
     * @param callable $handler The function that implements the tool's logic
     * @param string|null $description Optional description of the tool's purpose
     */
    public function addTool(string $name, callable $handler, ?string $description = null): void
    {
        $tool = new Tool($name, $handler, $description);
        $this->tools[$name] = $tool;
    }

    /**
     * Add a resource to the server.
     *
     * @param string $uri The URI identifier for the resource
     * @param mixed $content The resource content
     * @param string|null $mimeType Optional MIME type of the resource
     * @param string|null $description Optional description of the resource
     * @throws \RuntimeException If a resource with the given URI already exists
     */
    public function addResource(string $uri, mixed $content, ?string $mimeType = null, ?string $description = null): void
    {
        if ($this->hasResource($uri)) {
            throw new \RuntimeException("Resource already exists: {$uri}");
        }

        $handler = is_callable($content) ? $content : fn () => $content;
        $resource = new Resource($uri, $handler, $mimeType, $description);
        $this->resources[$uri] = $resource;
    }

    /**
     * Add a resource template to the server.
     *
     * @param string $uri The URI for the resource template
     * @param ResourceTemplateInterface $template The resource template to add
     * @throws RuntimeException If a resource with the given URI already exists
     */
    public function addResourceTemplate(string $uri, ResourceTemplateInterface $template): void
    {
        if ($this->hasResource($uri)) {
            throw new RuntimeException("Resource already exists: {$uri}");
        }

        $this->resourceTemplates[$uri] = $template;
        $this->resources[$uri] = new Resource($uri, fn () => $template->render());
    }

    /**
     * {@inheritdoc}
     */
    public function addPrompt(string $name, array $messages, ?string $description = null): void
    {
        $prompt = new Prompt($name, fn (array $args) => array_merge($messages, $args), $description);
        $prompt->setMessages($messages);
        $this->prompts[$name] = $prompt;
    }

    /**
     * {@inheritdoc}
     */
    public function addRoot(Root $root): void
    {
        $this->roots[$root->getPath()] = $root;
    }

    /**
     * {@inheritdoc}
     */
    public function handleCompletion(array $argument, array $ref): array
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport must be set before handling completion');
        }

        $this->transport->send([
            'type' => 'completion',
            'argument' => $argument,
            'ref' => $ref,
        ]);

        return $this->transport->receive();
    }

    /**
     * {@inheritdoc}
     */
    public function sendProgress(float $progress, string|int $progressToken, ?float $total = null): void
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport must be set before sending progress');
        }

        if ($total !== null && $total <= 0) {
            throw new InvalidArgumentException('Total must be greater than zero');
        }

        $this->transport->send([
            'type' => 'progress',
            'progress' => $progress,
            'token' => $progressToken,
            'total' => $total,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function sendLog(mixed $data, string $level, ?string $logger = null): void
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport must be set before sending logs');
        }

        $this->transport->send([
            'type' => 'log',
            'data' => $data,
            'level' => $level,
            'logger' => $logger,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getModelPreferences(): ModelPreferences
    {
        return $this->preferences;
    }

    /**
     * {@inheritdoc}
     */
    public function setModelPreferences(ModelPreferences $preferences): void
    {
        $this->preferences = $preferences;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): void
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport not initialized');
        }

        $this->transport->start();
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        if (! $this->transport) {
            throw new RuntimeException('Transport not initialized');
        }

        $this->transport->stop();
    }

    /**
     * {@inheritdoc}
     */
    public function addToolInterface(ToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * {@inheritdoc}
     */
    public function addResourceInterface(ResourceInterface $resource): void
    {
        $this->resources[$resource->getUri()] = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function addPromptInterface(PromptInterface $prompt): void
    {
        $this->prompts[$prompt->getName()] = $prompt;
    }

    /**
     * Get a registered resource by URI.
     *
     * @param string $uri The URI of the resource
     * @return ResourceInterface|null The resource or null if not found
     */
    public function getResource(string $uri): ?ResourceInterface
    {
        return $this->resources[$uri] ?? null;
    }

    /**
     * Get a registered prompt by name.
     *
     * @param string $name The name of the prompt
     * @return PromptInterface|null The prompt or null if not found
     */
    public function getPrompt(string $name): ?PromptInterface
    {
        return $this->prompts[$name] ?? null;
    }

    /**
     * Get the handler for a prompt.
     *
     * @param string $name The name of the prompt
     * @return callable|null The handler or null if not found
     */
    public function getHandler(string $name): ?callable
    {
        $prompt = $this->prompts[$name] ?? null;

        return $prompt ? $prompt->getHandler() : null;
    }

    /**
     * Register a new prompt with the server.
     *
     * @param string $name The name of the prompt
     * @param callable $handler The handler function for the prompt
     * @param array $arguments Optional arguments for the prompt
     * @return void
     */
    public function registerPrompt(string $name, callable $handler, array $arguments = []): void
    {
        $prompt = new Prompt($name, $handler);
        if (! empty($arguments)) {
            $prompt->setDefaultArguments($arguments);
        }
        $this->addPromptInterface($prompt);
    }

    /**
     * Check if a resource exists with the given URI.
     *
     * @param string $uri The URI to check
     * @return bool True if the resource exists, false otherwise
     */
    public function hasResource(string $uri): bool
    {
        return isset($this->resources[$uri]);
    }

    /**
     * Remove a resource with the given URI.
     *
     * @param string $uri The URI of the resource to remove
     * @throws RuntimeException If the resource does not exist
     */
    public function removeResource(string $uri): void
    {
        if (! $this->hasResource($uri)) {
            throw new RuntimeException("Resource not found: {$uri}");
        }
        unset($this->resources[$uri]);
    }
}
