<?php

namespace LaravelMCP\MCP\Capabilities;

/**
 * Represents the capabilities of an MCP server.
 *
 * This class manages information about the various capabilities and features
 * supported by an MCP server instance. It includes experimental features,
 * logging configuration, and support for prompts, resources, and tools.
 *
 * Capability Categories:
 * - Experimental: Beta or in-development features
 * - Logging: Log levels, formats, and destinations
 * - Prompts: Interactive message handling
 * - Resources: Content and file management
 * - Tools: Command execution and processing
 *
 * Configuration Format:
 * ```php
 * [
 *     'experimental' => [
 *         'feature_name' => true|false,
 *         // Other experimental flags
 *     ],
 *     'logging' => {
 *         'level' => 'debug|info|warning|error',
 *         'format' => 'json|text',
 *         'destination' => 'file|stdout'
 *     },
 *     'prompts' => [
 *         // Prompt configuration
 *     ],
 *     'resources' => [
 *         // Resource configuration
 *     ],
 *     'tools' => [
 *         // Tool configuration
 *     ]
 * ]
 * ```
 *
 * @package LaravelMCP\MCP\Capabilities
 */
class ServerCapabilities
{
    /**
     * Create a new server capabilities instance.
     *
     * Initializes a capabilities configuration with optional components.
     * Each component can be null if the capability is not supported or
     * not configured.
     *
     * Example:
     * ```php
     * $capabilities = new ServerCapabilities(
     *     experimental: ['new_feature' => true],
     *     logging: (object)['level' => 'debug'],
     *     prompts: new PromptsCapability(),
     *     resources: new ResourcesCapability(),
     *     tools: new ToolsCapability()
     * );
     * ```
     *
     * @param array|null $experimental Experimental features configuration
     * @param object|null $logging Logging configuration
     * @param PromptsCapability|null $prompts Prompts capability configuration
     * @param ResourcesCapability|null $resources Resources capability configuration
     * @param ToolsCapability|null $tools Tools capability configuration
     */
    public function __construct(
        private ?array $experimental = null,
        private ?object $logging = null,
        private ?PromptsCapability $prompts = null,
        private ?ResourcesCapability $resources = null,
        private ?ToolsCapability $tools = null
    ) {
    }

    /**
     * Get the experimental features configuration.
     *
     * Returns the configuration for experimental or beta features.
     * These features may not be stable and could change in future releases.
     *
     * @return array|null The experimental features configuration
     */
    public function getExperimental(): ?array
    {
        return $this->experimental;
    }

    /**
     * Get the logging configuration.
     *
     * Returns the configuration for log handling, including:
     * - Log levels (debug, info, warning, error)
     * - Output format (JSON, text)
     * - Destination (file, stdout)
     *
     * @return object|null The logging configuration
     */
    public function getLogging(): ?object
    {
        return $this->logging;
    }

    /**
     * Get the prompts capability configuration.
     *
     * Returns the configuration for handling interactive prompts,
     * including message formats, validation rules, and handlers.
     *
     * @return PromptsCapability|null The prompts capability
     */
    public function getPrompts(): ?PromptsCapability
    {
        return $this->prompts;
    }

    /**
     * Get the resources capability configuration.
     *
     * Returns the configuration for managing resources, including:
     * - File handling
     * - Content types
     * - Access control
     * - URI patterns
     *
     * @return ResourcesCapability|null The resources capability
     */
    public function getResources(): ?ResourcesCapability
    {
        return $this->resources;
    }

    /**
     * Get the tools capability configuration.
     *
     * Returns the configuration for available tools, including:
     * - Command handlers
     * - Parameter validation
     * - Execution rules
     * - Security settings
     *
     * @return ToolsCapability|null The tools capability
     */
    public function getTools(): ?ToolsCapability
    {
        return $this->tools;
    }

    /**
     * Convert the capabilities to an array format.
     *
     * Transforms the capabilities configuration into a structured array
     * that can be serialized or transmitted. Only includes non-null
     * capabilities in the output.
     *
     * Example output:
     * ```php
     * [
     *     'experimental' => ['feature' => true],
     *     'logging' => ['level' => 'debug'],
     *     'prompts' => [...],
     *     'resources' => [...],
     *     'tools' => [...]
     * ]
     * ```
     *
     * @return array The capabilities data as a key-value array
     */
    public function toArray(): array
    {
        $capabilities = [];

        if ($this->experimental !== null) {
            $capabilities['experimental'] = $this->experimental;
        }

        if ($this->logging !== null) {
            $capabilities['logging'] = $this->logging;
        }

        if ($this->prompts !== null) {
            $capabilities['prompts'] = $this->prompts->toArray();
        }

        if ($this->resources !== null) {
            $capabilities['resources'] = $this->resources->toArray();
        }

        if ($this->tools !== null) {
            $capabilities['tools'] = $this->tools->toArray();
        }

        return $capabilities;
    }

    /**
     * Create a new instance from an array of data.
     *
     * Factory method that constructs a ServerCapabilities instance from
     * a configuration array. This is useful for deserializing stored
     * configurations or processing API responses.
     *
     * Example:
     * ```php
     * $capabilities = ServerCapabilities::create([
     *     'experimental' => ['feature' => true],
     *     'logging' => ['level' => 'debug'],
     *     'prompts' => [...],
     *     'resources' => [...],
     *     'tools' => [...]
     * ]);
     * ```
     *
     * @param array $data The data to create the instance from
     * @return static A new instance of the capabilities
     */
    public static function create(array $data): static
    {
        return new static(
            experimental: $data['experimental'] ?? null,
            logging: $data['logging'] ?? null,
            prompts: isset($data['prompts']) ? PromptsCapability::create($data['prompts']) : null,
            resources: isset($data['resources']) ? ResourcesCapability::create($data['resources']) : null,
            tools: isset($data['tools']) ? ToolsCapability::create($data['tools']) : null
        );
    }
}
