<?php

namespace LaravelMCP\MCP;

/**
 * Represents a root directory in the MCP system.
 *
 * A root defines a base directory path that can be used as a reference point
 * for resolving relative paths and managing file system operations. Roots
 * help organize and manage file system access in a structured way.
 *
 * Roots can be used to:
 * - Define workspace boundaries
 * - Resolve relative paths
 * - Manage file system access
 * - Organize project structure
 *
 * @package LaravelMCP\MCP
 */
class Root
{
    /**
     * @var string The URI of the root directory
     */
    private string $uri;

    /**
     * @var string|null The name of the root
     */
    private ?string $name;

    /**
     * Create a new root instance.
     *
     * @param string $uri The URI of the root directory
     * @param string|null $name Optional name of the root
     * @throws \InvalidArgumentException If the URI is invalid
     */
    public function __construct(string $uri, ?string $name = null)
    {
        if (! str_starts_with($uri, 'file://')) {
            throw new \InvalidArgumentException('Root URI must start with file://');
        }
        $this->uri = $uri;
        $this->name = $name;
    }

    /**
     * Get the URI of the root directory.
     *
     * Returns the full URI of the root directory, including the 'file://' prefix.
     * This URI can be used for file system operations and path resolution.
     *
     * @return string The complete URI of the root directory
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the name of the root.
     *
     * Returns the optional name assigned to this root directory.
     * The name can be used for identification and organization purposes.
     *
     * @return string|null The root's name, or null if not set
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the filesystem path of the root directory.
     *
     * Converts the URI to a filesystem path by removing the 'file://' prefix.
     * This path can be used directly with filesystem operations.
     *
     * @return string The filesystem path
     */
    public function getPath(): string
    {
        return str_replace('file://', '', $this->uri);
    }

    /**
     * Convert the root to an array representation.
     *
     * Creates an array containing the root's properties for serialization
     * or data transfer. The array includes:
     * - uri: The complete URI of the root directory
     * - name: The root's name (if set)
     *
     * @return array The array representation of the root
     */
    public function toArray(): array
    {
        $data = [
            'uri' => $this->uri,
        ];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        return $data;
    }

    /**
     * Create a new root instance from an array.
     *
     * Factory method that creates a root instance from an array of data.
     * The array should contain:
     * - uri: The URI of the root directory (required)
     * - name: The name of the root (optional)
     *
     * @param array $data The data to create the root from
     * @return static A new root instance
     * @throws \InvalidArgumentException If required data is missing or invalid
     */
    public static function create(array $data): static
    {
        return new static(
            uri: $data['uri'],
            name: $data['name'] ?? null
        );
    }

    /**
     * Get the description of the root.
     *
     * This method is part of a common interface pattern but currently
     * returns null as root descriptions are not implemented.
     *
     * @return string|null Always returns null
     */
    public function getDescription(): ?string
    {
        return null;
    }

    /**
     * Resolve a relative path against this root.
     *
     * Combines the root's path with a relative path to create a complete
     * filesystem path. This method:
     * 1. Removes the 'file://' prefix from the root URI
     * 2. Combines it with the relative path
     * 3. Normalizes directory separators
     * 4. Ensures proper path separation
     *
     * Example:
     * Root URI: 'file:///var/www'
     * Relative path: 'app/config.php'
     * Result: '/var/www/app/config.php'
     *
     * @param string $relativePath The relative path to resolve
     * @return string The complete filesystem path
     */
    public function resolve(string $relativePath): string
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR);
    }
}
