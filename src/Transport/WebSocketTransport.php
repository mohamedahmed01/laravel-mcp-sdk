<?php

namespace LaravelMCP\MCP\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer as RatchetHttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use RuntimeException;

/**
 * WebSocket transport implementation for the MCP system.
 *
 * This transport provides WebSocket-based communication between clients and
 * the MCP server. It sets up a WebSocket server that can handle real-time,
 * bidirectional communication. The transport uses Ratchet and React PHP for
 * asynchronous WebSocket operations.
 *
 * Features:
 * - WebSocket protocol support
 * - Real-time bidirectional communication
 * - Message queueing
 * - Client connection management
 * - Asynchronous processing
 *
 * @package LaravelMCP\MCP\Transport
 */
class WebSocketTransport implements TransportInterface, MessageComponentInterface
{
    /**
     * @var LoopInterface The event loop instance
     */
    private LoopInterface $loop;

    /**
     * @var IoServer|null The WebSocket server instance
     */
    private ?IoServer $server = null;

    /**
     * @var mixed The message handler function
     */
    private mixed $messageHandler = null;

    /**
     * @var string The server host address
     */
    private string $host;

    /**
     * @var int The server port number
     */
    private int $port;

    /**
     * @var array<string, mixed> Connected clients
     */
    private array $clients = [];

    /**
     * @var array Message queue for pending messages
     */
    private array $messageQueue = [];

    /**
     * @var bool Indicates whether the transport is running
     */
    private bool $running = false;

    private MCPServerInterface $mcpServer;
    private SocketServer $socket;

    /**
     * Create a new WebSocket transport instance.
     *
     * @param LoopInterface $loop The event loop to use
     * @param MCPServerInterface $server The MCP server instance
     * @param array $config Optional configuration parameters
     */
    public function __construct(LoopInterface $loop, MCPServerInterface $server, array $config = [])
    {
        $this->loop = $loop;
        $this->mcpServer = $server;
        $this->host = $config['host'] ?? '127.0.0.1';
        $this->port = (int)($config['port'] ?? 8080);

        $this->socket = new SocketServer("{$this->host}:{$this->port}", [], $this->loop);
    }

    /**
     * Get the server host address.
     *
     * @return string The host address
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the server port number.
     *
     * @return int The port number
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function start(): void
    {
        if ($this->server !== null) {
            throw new RuntimeException('Transport already started');
        }

        $this->running = true;
        $this->server = new IoServer(
            new RatchetHttpServer(
                new WsServer($this)
            ),
            $this->socket,
            $this->loop
        );
        $this->server->run();
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        $this->running = false;
        foreach ($this->clients as $client) {
            $client->close();
        }
        $this->socket->close();
        $this->clients = [];
        $this->messageQueue = [];
    }

    /**
     * Send data to all connected clients.
     *
     * @param array $data The data to send
     */
    public function send(array $data): void
    {
        try {
            $encoded = json_encode($data, JSON_THROW_ON_ERROR);
            foreach ($this->clients as $client) {
                $client->send($encoded);
            }
        } catch (\JsonException $e) {
            throw new \RuntimeException('Failed to encode message: ' . $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): array
    {
        return array_shift($this->messageQueue) ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Handle a new WebSocket connection.
     *
     * @param ConnectionInterface $conn The connection instance
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients[$conn->resourceId] = $conn;
    }

    /**
     * Handle a closed WebSocket connection.
     *
     * @param ConnectionInterface $conn The connection instance
     */
    public function onClose(ConnectionInterface $conn): void
    {
        unset($this->clients[$conn->resourceId]);
    }

    /**
     * Handle a WebSocket connection error.
     *
     * @param ConnectionInterface $conn The connection instance
     * @param \Exception $e The error that occurred
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
        unset($this->clients[$conn->resourceId]);
    }

    /**
     * Handle an incoming WebSocket message.
     *
     * @param ConnectionInterface $from The connection that sent the message
     * @param string $msg The message content
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $data = json_decode($msg, true, 512, JSON_THROW_ON_ERROR);
            $response = $this->processMessage($data);
            $encoded = json_encode($response, JSON_THROW_ON_ERROR);
            $from->send($encoded);
        } catch (\JsonException $e) {
            $encoded = json_encode([
                'error' => 'Invalid JSON: ' . $e->getMessage(),
            ], JSON_THROW_ON_ERROR);
            $from->send($encoded);
        }
    }

    /**
     * Process an incoming message from a client.
     *
     * This method handles the core message processing logic:
     * 1. Validates the message structure
     * 2. Extracts type, name, and arguments
     * 3. Forwards the message to the MCP server
     * 4. Returns the processed result
     *
     * Expected message format:
     * {
     *     "type": string,      // The message type (required)
     *     "name": string,      // Resource name (optional)
     *     "arguments": object, // Message arguments (optional)
     *     "uri": string       // Resource URI (optional)
     * }
     *
     * @param array<string, mixed> $data The message data to process
     * @return array<string, mixed> The processing result
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

    /**
     * Set a custom message handler function.
     *
     * The handler will be called for each incoming message before standard processing.
     * It can be used to implement custom message handling logic or preprocessing.
     *
     * The handler function should have the following signature:
     * function(array $message): ?array
     *
     * If the handler returns null, standard message processing will continue.
     * If it returns an array, that array will be used as the response.
     *
     * @param callable $handler The message handler function
     */
    public function setMessageHandler(callable $handler): void
    {
        $this->messageHandler = $handler;
    }

    /**
     * Get the current message handler function.
     *
     * @return callable|null The current message handler or null if none set
     */
    public function getMessageHandler(): ?callable
    {
        return $this->messageHandler;
    }
}
