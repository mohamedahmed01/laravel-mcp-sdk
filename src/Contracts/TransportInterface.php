<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for MCP transport implementations.
 *
 * This interface defines the contract that all transport implementations
 * must follow. A transport is responsible for handling communication
 * between clients and the MCP server. It provides methods for:
 * - Starting and stopping the transport server
 * - Sending messages to clients
 * - Receiving messages from clients
 * - Checking transport status
 *
 * Implementations should handle:
 * - Message encoding/decoding (typically JSON)
 * - Connection management
 * - Error handling
 * - Resource cleanup
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface TransportInterface
{
    /**
     * Start the transport server.
     *
     * Initializes the transport and begins accepting client connections.
     * This method should:
     * - Set up any required server instances
     * - Initialize connection handling
     * - Start message processing
     *
     * @throws \RuntimeException If the transport fails to start
     */
    public function start(): void;

    /**
     * Stop the transport server.
     *
     * Gracefully shuts down the transport, closing all client connections
     * and cleaning up resources. This method should:
     * - Close all active connections
     * - Stop accepting new connections
     * - Clean up any allocated resources
     */
    public function stop(): void;

    /**
     * Send a message to the client.
     *
     * Sends data to one or more connected clients. The message should be
     * encoded in a format that clients can understand (typically JSON).
     *
     * @param array $message The message data to send
     * @throws \RuntimeException If message sending fails
     */
    public function send(array $message): void;

    /**
     * Receive a message from the client.
     *
     * Retrieves the next message from the transport's message queue.
     * If no message is available, returns an empty array.
     *
     * @return array The received message data or an empty array
     */
    public function receive(): array;

    /**
     * Check if the transport is running.
     *
     * Returns whether the transport is currently active and able to
     * handle client connections and messages.
     *
     * @return bool True if the transport is running, false otherwise
     */
    public function isRunning(): bool;
}
