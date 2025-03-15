<?php

namespace LaravelMCP\MCP\Capabilities\ServerCapabilities;

/**
 * Represents the resources capability for server-side functionality.
 *
 * This class manages information about server-side resource capabilities,
 * including whether resource handling is enabled and what resources are
 * available on the server.
 *
 * @package LaravelMCP\MCP\Capabilities\ServerCapabilities
 */
class ResourcesCapability
{
    /**
     * Create a new resources capability instance.
     *
     * @param bool $enabled Whether resource handling is enabled on the server
     * @param array $resources List of available server resources
     */
    public function __construct(
        private bool $enabled = false,
        private array $resources = []
    ) {
    }

    /**
     * Check if resource handling is enabled on the server.
     *
     * @return bool True if resource handling is enabled, false otherwise
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the list of available server resources.
     *
     * @return array List of configured server resources
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * Convert the capability to an array format.
     *
     * @return array The capability data as a key-value array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'resources' => $this->resources,
        ];
    }

    /**
     * Create a new instance from an array of data.
     *
     * @param array $data The data to create the instance from
     * @return static A new instance of the capability
     */
    public static function create(array $data): static
    {
        return new static(
            enabled: $data['enabled'] ?? false,
            resources: $data['resources'] ?? []
        );
    }
}
