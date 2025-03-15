<?php

namespace LaravelMCP\MCP\Capabilities;

/**
 * Represents the tools capability in the MCP system.
 *
 * This class manages information about changes to the available tools
 * in the MCP system. It tracks whether the list of tools has been
 * modified and provides methods to serialize and deserialize this state.
 *
 * Tools Capability Features:
 * - Tool list change tracking
 * - State serialization/deserialization
 * - Change notification support
 *
 * Use Cases:
 * - Detecting when tools are added or removed
 * - Synchronizing tool lists between client and server
 * - Managing tool state across sessions
 *
 * Example Usage:
 * ```php
 * // Create a capability instance
 * $tools = new ToolsCapability(listChanged: true);
 *
 * // Check if tools have changed
 * if ($tools->getListChanged()) {
 *     // Handle tool list changes
 * }
 *
 * // Convert to array for storage/transmission
 * $data = $tools->toArray();
 * ```
 *
 * @package LaravelMCP\MCP\Capabilities
 */
class ToolsCapability
{
    /**
     * Create a new tools capability instance.
     *
     * Initializes a tools capability tracker that monitors changes
     * to the available tools in the MCP system.
     *
     * Example:
     * ```php
     * // Track changes to tool list
     * $tools = new ToolsCapability(listChanged: true);
     *
     * // Initialize with unknown state
     * $tools = new ToolsCapability();
     * ```
     *
     * @param bool|null $listChanged Whether the list of tools has changed
     *                              - true: Tools list has been modified
     *                              - false: Tools list is unchanged
     *                              - null: Change state is unknown
     */
    public function __construct(
        private ?bool $listChanged = null
    ) {
    }

    /**
     * Get whether the list of tools has changed.
     *
     * Retrieves the current state of tool list modifications.
     * This can be used to determine if the available tools
     * have been updated since the last synchronization.
     *
     * Example:
     * ```php
     * if ($tools->getListChanged() === true) {
     *     // Tools have been added or removed
     *     // Trigger resynchronization
     * } elseif ($tools->getListChanged() === false) {
     *     // No changes to tool list
     * } else {
     *     // Change state is unknown
     * }
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
     * Transforms the tools capability state into a structured array
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
        $tools = [];

        if ($this->listChanged !== null) {
            $tools['listChanged'] = $this->listChanged;
        }

        return $tools;
    }

    /**
     * Create a new instance from an array of data.
     *
     * Factory method that constructs a ToolsCapability instance
     * from a configuration array. This is useful for deserializing
     * stored configurations or processing API responses.
     *
     * Example:
     * ```php
     * $tools = ToolsCapability::create([
     *     'listChanged' => true
     * ]);
     * ```
     *
     * @param array $data The data to create the instance from
     * @return static A new instance of the capability
     */
    public static function create(array $data): static
    {
        return new static(
            listChanged: $data['listChanged'] ?? null
        );
    }
}
