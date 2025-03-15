<?php

namespace LaravelMCP\MCP\Capabilities;

/**
 * Represents the resources capability in the MCP system.
 *
 * This class manages information about resource-related features in the MCP system.
 * It tracks whether resource subscription is enabled and whether the list of
 * available resources has changed.
 *
 * Resource Capability Features:
 * - Resource subscription management
 * - Change tracking for resource lists
 * - State serialization/deserialization
 * - Subscription status monitoring
 *
 * Resource Management:
 * - Subscribe to resource updates
 * - Track resource list modifications
 * - Monitor resource availability
 * - Handle resource state changes
 *
 * Example Usage:
 * ```php
 * // Create a resources capability with subscription
 * $resources = new ResourcesCapability(
 *     subscribe: true,
 *     listChanged: false
 * );
 *
 * // Check subscription status
 * if ($resources->getSubscribe()) {
 *     // Handle resource updates
 * }
 *
 * // Monitor resource list changes
 * if ($resources->getListChanged()) {
 *     // Synchronize resource list
 * }
 * ```
 *
 * @package LaravelMCP\MCP\Capabilities
 */
class ResourcesCapability
{
    /**
     * Create a new resources capability instance.
     *
     * Initializes the resources capability with optional configuration
     * for resource subscription and change tracking. Both parameters
     * can be null to indicate an unknown state.
     *
     * Example:
     * ```php
     * // Enable subscription and track changes
     * $resources = new ResourcesCapability(
     *     subscribe: true,
     *     listChanged: false
     * );
     *
     * // Initialize with unknown state
     * $resources = new ResourcesCapability();
     *
     * // Enable subscription only
     * $resources = new ResourcesCapability(subscribe: true);
     * ```
     *
     * @param bool|null $subscribe Whether resource subscription is enabled
     *                            - true: Resource updates will be received
     *                            - false: No resource updates
     *                            - null: Subscription state unknown
     * @param bool|null $listChanged Whether the list of resources has changed
     *                              - true: Resources have been added/removed
     *                              - false: No changes to resource list
     *                              - null: Change state unknown
     */
    public function __construct(
        private ?bool $subscribe = null,
        private ?bool $listChanged = null
    ) {
    }

    /**
     * Get whether resource subscription is enabled.
     *
     * Determines if the system is configured to receive updates about
     * resource changes. When enabled, the system will be notified of
     * resource modifications, additions, and removals.
     *
     * Example:
     * ```php
     * $subscription = $resources->getSubscribe();
     * match ($subscription) {
     *     true => 'Resource updates enabled',
     *     false => 'Resource updates disabled',
     *     null => 'Subscription state unknown'
     * };
     * ```
     *
     * @return bool|null True if subscription is enabled, false if not, null if unknown
     */
    public function getSubscribe(): ?bool
    {
        return $this->subscribe;
    }

    /**
     * Get whether the list of resources has changed.
     *
     * Checks if there have been modifications to the available resources
     * since the last synchronization. This can include additions, removals,
     * or updates to existing resources.
     *
     * Example:
     * ```php
     * if ($resources->getListChanged() === true) {
     *     // Resources have been modified
     *     // Trigger resynchronization
     * } elseif ($resources->getListChanged() === false) {
     *     // No changes to resources
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
     * Transforms the resources capability state into a structured array
     * suitable for storage or transmission. Only includes properties
     * that have been explicitly set (non-null values).
     *
     * Example output:
     * ```php
     * [
     *     'subscribe' => true,      // Only if set
     *     'listChanged' => false    // Only if set
     * ]
     * ```
     *
     * @return array The capability data as a key-value array
     */
    public function toArray(): array
    {
        $resources = [];

        if ($this->subscribe !== null) {
            $resources['subscribe'] = $this->subscribe;
        }

        if ($this->listChanged !== null) {
            $resources['listChanged'] = $this->listChanged;
        }

        return $resources;
    }

    /**
     * Create a new instance from an array of data.
     *
     * Factory method that constructs a ResourcesCapability instance
     * from a configuration array. This is useful for deserializing
     * stored configurations or processing API responses.
     *
     * Example:
     * ```php
     * $resources = ResourcesCapability::create([
     *     'subscribe' => true,
     *     'listChanged' => false
     * ]);
     * ```
     *
     * @param array $data The data to create the instance from, containing:
     *                    - subscribe: bool|null - Subscription status
     *                    - listChanged: bool|null - Change tracking status
     * @return static A new instance of the capability
     */
    public static function create(array $data): static
    {
        return new static(
            subscribe: $data['subscribe'] ?? null,
            listChanged: $data['listChanged'] ?? null
        );
    }
}
