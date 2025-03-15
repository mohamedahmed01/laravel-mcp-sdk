<?php

namespace LaravelMCP\MCP;

/**
 * Base class for MCP system implementations.
 *
 * This class provides a foundation for implementing various components
 * in the MCP system. It includes common functionality and utilities
 * that can be shared across different implementations.
 *
 * The class supports:
 * - Serialization to array format
 * - Creation from array data
 * - Common implementation patterns
 *
 * @package LaravelMCP\MCP
 */
class Implementation
{
    /**
     * Create a new implementation instance.
     *
     * @param string $name The name of the implementation
     * @param string $version The version identifier of the implementation
     */
    public function __construct(
        private string $name,
        private string $version
    ) {
    }

    /**
     * Get the implementation's name.
     *
     * Returns the name that identifies this implementation.
     * This is typically used for registration and lookup purposes.
     *
     * @return string The implementation name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the implementation's version.
     *
     * Returns the version identifier of this implementation.
     * This can be used for version checking and compatibility.
     *
     * @return string The version identifier
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Convert the implementation to an array format.
     *
     * Creates an array containing the implementation's core properties.
     * The base implementation includes:
     * - name: The implementation name
     * - version: The version identifier
     *
     * Child classes can override this method to include additional
     * properties specific to their implementation.
     *
     * @return array The implementation data as a key-value array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
        ];
    }

    /**
     * Create a new instance from an array of data.
     *
     * Factory method that creates an implementation instance from
     * an array of data. The array must contain:
     * - name: The implementation name
     * - version: The version identifier
     *
     * @param array $data The data to create the instance from
     * @return static A new instance of the implementation
     * @throws \InvalidArgumentException If required data is missing
     */
    public static function create(array $data): static
    {
        return new static(
            name: $data['name'],
            version: $data['version']
        );
    }
}
