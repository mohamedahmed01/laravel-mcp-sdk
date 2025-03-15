<?php

namespace LaravelMCP\MCP\Capabilities;

/**
 * Represents the prompts capability in the MCP system.
 *
 * This class manages information about changes to the available prompts
 * in the MCP system. It tracks whether the list of prompts has been
 * modified and provides methods to serialize and deserialize this state.
 *
 * Prompts Capability Features:
 * - Track changes to available prompts
 * - Monitor prompt list modifications
 * - State serialization/deserialization
 * - Change notification support
 *
 * Use Cases:
 * - Detecting when prompts are added or removed
 * - Synchronizing prompt lists between client and server
 * - Managing prompt state across sessions
 * - Handling prompt updates and notifications
 *
 * Example Usage:
 * ```php
 * // Create a prompts capability instance
 * $prompts = new PromptsCapability(listChanged: true);
 *
 * // Check if prompts have changed
 * if ($prompts->getListChanged()) {
 *     // Handle prompt list changes
 *     // e.g., refresh available prompts
 * }
 *
 * // Convert to array for storage/transmission
 * $data = $prompts->toArray();
 * ```
 *
 * @package LaravelMCP\MCP\Capabilities
 */
class PromptsCapability
{
    /**
     * Create a new prompts capability instance.
     *
     * Initializes a prompts capability tracker that monitors changes
     * to the available prompts in the MCP system. The change state
     * can be explicitly set or left as null for unknown state.
     *
     * Example:
     * ```php
     * // Track changes to prompt list
     * $prompts = new PromptsCapability(listChanged: true);
     *
     * // Initialize with unknown state
     * $prompts = new PromptsCapability();
     *
     * // Explicitly set no changes
     * $prompts = new PromptsCapability(listChanged: false);
     * ```
     *
     * @param bool|null $listChanged Whether the list of prompts has changed
     *                              - true: Prompts have been added/removed
     *                              - false: No changes to prompts
     *                              - null: Change state is unknown
     */
    public function __construct(
        private ?bool $listChanged = null
    ) {
    }

    /**
     * Get whether the list of prompts has changed.
     *
     * Retrieves the current state of prompt list modifications.
     * This can be used to determine if the available prompts
     * have been updated since the last synchronization.
     *
     * Example:
     * ```php
     * $changes = $prompts->getListChanged();
     * match ($changes) {
     *     true => 'Prompts have been modified',
     *     false => 'No changes to prompts',
     *     null => 'Change state unknown'
     * };
     * ```
     *
     * @return bool|null True if the list has changed, false if not, null if unknown
     */
    public function getListChanged(): ?bool
    {
        return $this->listChanged;
    }

    /**
     * Convert the capability to an array format.
     *
     * Transforms the prompts capability state into a structured array
     * suitable for storage or transmission. Only includes the listChanged
     * property if it has been set to a non-null value.
     *
     * Example output:
     * ```php
     * [
     *     'listChanged' => true  // Only included if set
     * ]
     * ```
     *
     * @return array The capability data as a key-value array
     */
    public function toArray(): array
    {
        $prompts = [];

        if ($this->listChanged !== null) {
            $prompts['listChanged'] = $this->listChanged;
        }

        return $prompts;
    }

    /**
     * Create a new instance from array data.
     *
     * Factory method that constructs a PromptsCapability instance
     * from a configuration array. This is useful for deserializing
     * stored configurations or processing API responses.
     *
     * Example:
     * ```php
     * $prompts = PromptsCapability::create([
     *     'listChanged' => true
     * ]);
     *
     * // Or with no changes
     * $prompts = PromptsCapability::create([
     *     'listChanged' => false
     * ]);
     * ```
     *
     * @param array $data The data to create the instance from, containing:
     *                    - listChanged: bool|null - Change tracking status
     * @return static A new instance of the capability
     */
    public static function create(array $data): static
    {
        return new static(
            listChanged: $data['listChanged'] ?? null
        );
    }
}
