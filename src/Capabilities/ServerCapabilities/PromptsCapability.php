<?php

namespace LaravelMCP\MCP\Capabilities\ServerCapabilities;

/**
 * Represents the prompts capability for server-side functionality.
 *
 * This class manages information about server-side prompt capabilities,
 * including whether prompt handling is enabled and what prompts are
 * available on the server.
 *
 * @package LaravelMCP\MCP\Capabilities\ServerCapabilities
 */
class PromptsCapability
{
    /**
     * Create a new prompts capability instance.
     *
     * @param bool $enabled Whether prompt handling is enabled on the server
     * @param array $prompts List of available server prompts
     */
    public function __construct(
        private bool $enabled = false,
        private array $prompts = []
    ) {
    }

    /**
     * Check if prompt handling is enabled on the server.
     *
     * @return bool True if prompt handling is enabled, false otherwise
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the list of available server prompts.
     *
     * @return array List of configured server prompts
     */
    public function getPrompts(): array
    {
        return $this->prompts;
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
            'prompts' => $this->prompts,
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
            prompts: $data['prompts'] ?? []
        );
    }
}
