<?php

namespace LaravelMCP\MCP\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

/**
 * Standard I/O transport implementation for the MCP system.
 *
 * This transport provides communication between clients and the MCP server
 * through standard input/output streams. It's particularly useful for
 * command-line applications and testing. The transport uses React PHP's
 * event loop for asynchronous I/O operations.
 *
 * Features:
 * - Non-blocking I/O
 * - JSON message encoding/decoding
 * - Event-driven processing
 * - Simple integration with CLI tools
 *
 * @package LaravelMCP\MCP\Transport
 */
class StdioTransport implements TransportInterface
{
    /** @var LoopInterface The event loop instance */
    private LoopInterface $loop;

    /** @var MCPServerInterface The MCP server instance */
    private MCPServerInterface $mcpServer;

    /** @var bool Whether the transport is running */
    protected bool $running = false;

    /** @var array Message queue for pending messages */
    protected array $messageQueue = [];

    /**
     * Create a new standard I/O transport instance.
     *
     * @param MCPServerInterface $server The MCP server instance
     */
    public function __construct(MCPServerInterface $server)
    {
        $this->loop = Loop::get();
        $this->mcpServer = $server;
    }

    /**
     * Start the transport.
     *
     * Sets up non-blocking I/O streams and starts the event loop to
     * process incoming messages from standard input.
     */
    public function start(): void
    {
        stream_set_blocking(STDIN, false);
        stream_set_blocking(STDOUT, false);

        $this->running = true;
        $this->loop->addReadStream(STDIN, function ($stream) {
            $line = fgets($stream);
            if ($line === false) {
                return;
            }

            /** @var array<string, mixed>|null $data */
            $data = json_decode($line, true);
            if (! is_array($data)) {
                return;
            }

            $response = $this->processMessage($data);
            $this->send($response);
        });

        $this->loop->run();
    }

    /**
     * Stop the transport.
     *
     * Stops the event loop and marks the transport as not running.
     */
    public function stop(): void
    {
        $this->running = false;
        $this->loop->stop();
    }

    /**
     * Send a message through the transport.
     *
     * Encodes the message as JSON and writes it to standard output.
     *
     * @param array $data The message data to send
     * @throws \RuntimeException If message encoding fails
     */
    public function send(array $data): void
    {
        $this->messageQueue[] = $data;
        $encoded = json_encode($data);
        if ($encoded === false) {
            throw new \RuntimeException('Failed to encode message');
        }
        fwrite(STDOUT, $encoded . PHP_EOL);
    }

    /**
     * Receive a message from the transport.
     *
     * @return array The next message in the queue or an empty array if none
     */
    public function receive(): array
    {
        return array_shift($this->messageQueue) ?? [];
    }

    /**
     * Check if the transport is running.
     *
     * @return bool True if the transport is running, false otherwise
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Handle an incoming message.
     *
     * Processes the message by decoding it from JSON and passing it to
     * the message handler. Sends the handler's response back.
     *
     * Error handling:
     * - Invalid JSON: Returns {"error": "Invalid JSON"}
     * - Processing error: Returns {"error": "error message"}
     *
     * @param string $msg The raw message to process
     */
    public function onMessage(string $msg): void
    {
        /** @var array<string, mixed>|null $data */
        $data = json_decode($msg, true);
        if (! is_array($data)) {
            $this->send(['error' => 'Invalid JSON']);

            return;
        }

        try {
            /** @var array<string, mixed> $response */
            $response = $this->processMessage($data);
            $this->send($response);
        } catch (\Exception $e) {
            $this->send(['error' => $e->getMessage()]);
        }
    }

    /**
     * Process a decoded message.
     *
     * Routes the message to the appropriate handler based on its type
     * and returns the handler's response.
     *
     * Supported message types:
     * - tool_call: Handles tool execution requests
     * - resource_request: Handles resource access requests
     * - prompt_request: Handles prompt generation requests
     *
     * Message format:
     * {
     *     "type": string,      // Message type (required)
     *     "name": string,      // Tool/prompt name (optional)
     *     "arguments": object, // Parameters (optional)
     *     "uri": string       // Resource URI (optional)
     * }
     *
     * @param array<string, mixed> $data The decoded message data
     * @return array<string, mixed> The handler's response
     * @throws \RuntimeException If the message type is unknown
     */
    protected function processMessage(array $data): array
    {
        /** @var array{
         *     type: string,
         *     name?: string,
         *     arguments?: array<string, mixed>,
         *     uri?: string
         * } $data
         */
        $type = $data['type'];
        $name = $data['name'] ?? '';
        $arguments = $data['arguments'] ?? [];
        $uri = $data['uri'] ?? '';

        /** @var array<string, mixed> */
        return match ($type) {
            'tool_call' => $this->mcpServer->handleToolCall(
                $name,
                $arguments
            ),
            'resource_request' => $this->mcpServer->handleResourceRequest(
                $uri
            ),
            'prompt_request' => $this->mcpServer->handlePromptRequest(
                $name,
                $arguments
            ),
            default => throw new \RuntimeException('Unknown message type')
        };
    }
}
