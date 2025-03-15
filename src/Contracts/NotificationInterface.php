<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for handling notifications in the MCP system.
 *
 * A notification represents a message sent from the MCP server to clients
 * to communicate events, status updates, or other information that doesn't
 * require a response. Notifications are one-way communications used for
 * logging, progress updates, and system events.
 *
 * Notifications can be used to:
 * - Report progress updates
 * - Send log messages
 * - Broadcast system events
 * - Provide status information
 * - Alert about errors or warnings
 * - Notify about task completion
 * - Broadcast configuration changes
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface NotificationInterface
{
    /**
     * Get the type of the notification.
     *
     * The type identifies what kind of notification this is and determines
     * how it should be handled by clients.
     *
     * Common notification types:
     * - progress: Task progress updates (0-100%)
     * - log: System log messages (info, warning, error)
     * - event: System events (start, stop, restart)
     * - status: Component status changes
     * - alert: Important user notifications
     * - task: Task-related updates
     * - config: Configuration changes
     *
     * @return string The notification type identifier
     */
    public function getType(): string;

    /**
     * Get the data associated with the notification.
     *
     * The data provides the actual content of the notification, such as
     * progress values, log messages, or event details.
     *
     * Common data structures:
     * Progress:
     * {
     *     "percent": int,      // Progress percentage (0-100)
     *     "message": string,   // Progress message
     *     "step": int,        // Current step number
     *     "total_steps": int  // Total number of steps
     * }
     *
     * Log:
     * {
     *     "level": string,    // Log level (info, warning, error)
     *     "message": string,  // Log message
     *     "context": array    // Additional context data
     * }
     *
     * Event:
     * {
     *     "name": string,     // Event name
     *     "timestamp": int,   // Event timestamp
     *     "data": mixed      // Event-specific data
     * }
     *
     * @return array The notification data
     */
    public function getData(): array;
}
