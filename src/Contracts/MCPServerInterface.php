<?php

namespace LaravelMCP\MCP\Contracts;

use LaravelMCP\MCP\Root;
use LaravelMCP\MCP\Sampling\ModelPreferences;

/**
 * The main interface for the MCP (Model-Controller-Prompt) server.
 *
 * This interface defines the core functionality for managing tools, resources,
 * prompts, and handling various types of requests in the MCP system. It provides
 * methods for server initialization, component registration, request handling,
 * and server lifecycle management.
 *
 * Core responsibilities:
 * - Server lifecycle management (start, stop, initialize)
 * - Component registration (tools, resources, prompts)
 * - Request handling (tool calls, resource access, prompts)
 * - Progress and logging management
 * - Transport layer configuration
 *
 * Component types:
 * - Tools: Executable functions with defined parameters
 * - Resources: Content accessible via URIs
 * - Prompts: Interactive message sequences
 * - Templates: Resource generation patterns
 * - Roots: File system access points
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface MCPServerInterface
{
    /**
     * Initialize the MCP server.
     *
     * This method should be called before starting the server to ensure all
     * required components are properly set up. Initialization includes:
     * - Transport layer configuration
     * - Component registration
     * - Resource loading
     * - Event handler setup
     *
     * @throws \RuntimeException If the transport is not set
     */
    public function initialize(): void;

    /**
     * Register a new tool with the server.
     *
     * Tools are executable functions that provide specific functionality.
     * Each tool should:
     * - Have a unique name
     * - Implement a specific operation
     * - Accept defined parameters
     * - Return consistent results
     *
     * @param string $name The unique identifier for the tool
     * @param callable $handler The function that implements the tool's logic
     * @param array $parameters Optional parameters for the tool
     * @throws \InvalidArgumentException If the tool name is already taken
     */
    public function registerTool(string $name, callable $handler, array $parameters = []): void;

    /**
     * Register a new resource with the server.
     *
     * Resources represent content that can be accessed via URIs. They can be:
     * - Static files
     * - Dynamic content
     * - Generated data
     * - External services
     *
     * @param string $uri The URI identifier for the resource
     * @param callable $handler The function that handles resource requests
     * @throws \InvalidArgumentException If the URI is already registered
     */
    public function registerResource(string $uri, callable $handler): void;

    /**
     * Register a new prompt with the server.
     *
     * Prompts are interactive message sequences that can:
     * - Collect user input
     * - Process data
     * - Generate responses
     * - Guide workflows
     *
     * @param string $name The unique identifier for the prompt
     * @param callable $handler The function that processes the prompt
     * @param array $arguments Optional arguments for the prompt
     * @throws \InvalidArgumentException If the prompt name is already taken
     */
    public function registerPrompt(string $name, callable $handler, array $arguments = []): void;

    /**
     * Get the server's capabilities.
     *
     * Returns a map of supported features and their availability status.
     * Common capabilities include:
     * - tool_support: Whether tools can be registered
     * - resource_support: Whether resources can be registered
     * - prompt_support: Whether prompts can be registered
     * - template_support: Whether templates can be used
     * - progress_support: Whether progress reporting is available
     *
     * @return array<string, bool> Array of capability flags
     */
    public function getCapabilities(): array;

    /**
     * Handle a tool call request.
     *
     * Executes a registered tool with the provided arguments. The process:
     * 1. Validates the tool exists
     * 2. Checks argument requirements
     * 3. Executes the tool handler
     * 4. Returns the result
     *
     * @param string $name The name of the tool to call
     * @param array $arguments Arguments to pass to the tool
     * @return mixed The result of the tool execution
     * @throws \RuntimeException If the tool is not found
     * @throws \InvalidArgumentException If arguments are invalid
     */
    public function handleToolCall(string $name, array $arguments): mixed;

    /**
     * Handle a resource request.
     *
     * Processes a request for a registered resource. The process:
     * 1. Resolves the resource URI
     * 2. Validates access permissions
     * 3. Retrieves or generates content
     * 4. Returns the resource data
     *
     * @param string $uri The URI of the requested resource
     * @return mixed The requested resource data
     * @throws \RuntimeException If the resource is not found
     * @throws \InvalidArgumentException If the URI is invalid
     */
    public function handleResourceRequest(string $uri): mixed;

    /**
     * Handle a prompt request.
     *
     * Processes a registered prompt with the provided arguments. The process:
     * 1. Validates the prompt exists
     * 2. Prepares the message sequence
     * 3. Processes the arguments
     * 4. Generates the response
     *
     * @param string $name The name of the prompt to handle
     * @param array $arguments Arguments for the prompt
     * @return mixed The prompt response
     * @throws \RuntimeException If the prompt is not found
     * @throws \InvalidArgumentException If arguments are invalid
     */
    public function handlePromptRequest(string $name, array $arguments): mixed;

    /**
     * Get all registered tools.
     *
     * @return array<string, ToolInterface> Map of tool names to their implementations
     */
    public function getTools(): array;

    /**
     * Get all registered resources.
     *
     * @return array<string, ResourceInterface> Map of resource URIs to their implementations
     */
    public function getResources(): array;

    /**
     * Get all registered resource templates.
     *
     * @return array<string, ResourceTemplateInterface> Map of template names to their implementations
     */
    public function getResourceTemplates(): array;

    /**
     * Get all registered prompts.
     *
     * @return array<string, PromptInterface> Map of prompt names to their implementations
     */
    public function getPrompts(): array;

    /**
     * Get all registered roots.
     *
     * @return array<string, Root> Map of root paths to their implementations
     */
    public function getRoots(): array;

    /**
     * Add a tool to the server.
     *
     * @param string $name The unique identifier for the tool
     * @param callable $handler The function that implements the tool's logic
     * @param string|null $description Optional description of the tool's purpose
     */
    public function addTool(string $name, callable $handler, ?string $description = null): void;

    /**
     * Add a resource to the server.
     *
     * @param string $uri The URI identifier for the resource
     * @param mixed $content The resource content
     * @param string|null $mimeType Optional MIME type of the resource
     * @param string|null $description Optional description of the resource
     */
    public function addResource(string $uri, mixed $content, ?string $mimeType = null, ?string $description = null): void;

    /**
     * Add a resource template to the server.
     *
     * @param string $uri The URI for the resource template
     * @param ResourceTemplateInterface $template The resource template to add
     * @throws \RuntimeException If a resource with the given URI already exists
     */
    public function addResourceTemplate(string $uri, ResourceTemplateInterface $template): void;

    /**
     * Add a prompt to the server.
     *
     * @param string $name The unique identifier for the prompt
     * @param array $messages The messages that make up the prompt
     * @param string|null $description Optional description of the prompt
     */
    public function addPrompt(string $name, array $messages, ?string $description = null): void;

    /**
     * Add a root to the server.
     *
     * @param Root $root The root to add
     */
    public function addRoot(Root $root): void;

    /**
     * Handle a completion request.
     *
     * @param array $argument The completion arguments
     * @param array $ref Reference data for the completion
     * @return array The completion response
     */
    public function handleCompletion(array $argument, array $ref): array;

    /**
     * Send a progress notification.
     *
     * Reports progress for long-running operations. Progress values should:
     * - Be between 0.0 and 1.0
     * - Increase monotonically
     * - Reflect actual operation progress
     *
     * Example usage:
     * ```php
     * // Start operation
     * $token = 'operation_123';
     * $server->sendProgress(0.0, $token, 100);
     *
     * // Update progress
     * $server->sendProgress(0.5, $token, 100);
     *
     * // Complete operation
     * $server->sendProgress(1.0, $token, 100);
     * ```
     *
     * @param float $progress The current progress value (0.0 to 1.0)
     * @param string|int $progressToken A unique token identifying the progress operation
     * @param float|null $total Optional total value for the progress
     * @throws \RuntimeException If transport is not set
     * @throws \InvalidArgumentException If progress value is invalid
     */
    public function sendProgress(float $progress, string|int $progressToken, ?float $total = null): void;

    /**
     * Send a logging message.
     *
     * Records a log message with the specified level and optional context.
     * Log levels follow PSR-3 standards:
     * - emergency: System is unusable
     * - alert: Action must be taken immediately
     * - critical: Critical conditions
     * - error: Error conditions
     * - warning: Warning conditions
     * - notice: Normal but significant events
     * - info: Informational messages
     * - debug: Debug-level messages
     *
     * @param mixed $data The log data
     * @param string $level The log level (e.g., 'info', 'error', 'debug')
     * @param string|null $logger Optional logger identifier
     * @throws \RuntimeException If transport is not set
     * @throws \InvalidArgumentException If log level is invalid
     */
    public function sendLog(mixed $data, string $level, ?string $logger = null): void;

    /**
     * Set model preferences for sampling.
     *
     * @param ModelPreferences $preferences The model preferences to set
     */
    public function setModelPreferences(ModelPreferences $preferences): void;

    /**
     * Start the server.
     *
     * @throws \RuntimeException If transport is not initialized
     */
    public function start(): void;

    /**
     * Stop the server.
     *
     * @throws \RuntimeException If transport is not initialized
     */
    public function stop(): void;

    /**
     * Set the transport layer for the server.
     *
     * @param TransportInterface $transport The transport to use
     */
    public function setTransport(TransportInterface $transport): void;

    /**
     * Get the current transport layer.
     *
     * @return TransportInterface|null The current transport or null if not set
     */
    public function getTransport(): ?TransportInterface;
}
