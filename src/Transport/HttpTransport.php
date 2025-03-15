<?php

namespace LaravelMCP\MCP\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

/**
 * HTTP server-based transport implementation for the MCP system.
 *
 * This transport creates an HTTP server that listens for incoming requests
 * and forwards them to the MCP server. It supports:
 * - HTTP/HTTPS communication
 * - Request/response handling
 * - Message queueing
 * - Multiple client connections
 *
 * @package LaravelMCP\MCP\Transport
 */
class HttpTransport implements TransportInterface
{
    /**
     * @var HttpServer The React HTTP server instance
     */
    private HttpServer $server;

    /**
     * @var SocketServer The React socket server instance
     */
    private SocketServer $socket;

    /**
     * @var MCPServerInterface The MCP server instance
     */
    private MCPServerInterface $mcpServer;

    /**
     * @var bool Whether the transport is currently running
     */
    protected bool $running = false;

    /**
     * @var array Queue of messages pending processing
     */
    protected array $messageQueue = [];

    /**
     * @var array Connected clients
     */
    protected array $clients = [];

    /**
     * Create a new HTTP transport instance.
     *
     * Initializes the HTTP server with the provided MCP server instance
     * and configuration. The server is configured to:
     * - Listen on the specified host and port
     * - Handle incoming HTTP requests
     * - Forward requests to the MCP server
     *
     * @param MCPServerInterface $server The MCP server instance
     * @param array $config Configuration options:
     *                     - host: string - The host to listen on (default: 127.0.0.1)
     *                     - port: int - The port to listen on (default: 8080)
     */
    public function __construct(MCPServerInterface $server, array $config = [])
    {
        $this->mcpServer = $server;
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 8080;

        $this->socket = new SocketServer("{$host}:{$port}");
        $this->server = new HttpServer([$this, 'handleRequest']);
    }

    /**
     * Start the transport.
     *
     * Starts the HTTP server, begins listening for connections,
     * and runs the event loop.
     */
    public function start(): void
    {
        $this->running = true;
        $this->server->listen($this->socket);
        Loop::run();
    }

    /**
     * Stop the transport.
     *
     * Stops the HTTP server, closes all connections,
     * and stops the event loop.
     */
    public function stop(): void
    {
        $this->running = false;
        $this->socket->close();
        Loop::stop();
    }

    /**
     * Send data to connected clients.
     *
     * Adds the data to the message queue for processing.
     * The data will be sent to clients on their next request.
     *
     * @param array $data The data to send
     */
    public function send(array $data): void
    {
        $this->messageQueue[] = $data;
    }

    /**
     * Receive data from the message queue.
     *
     * Returns and removes the next message from the queue.
     * If the queue is empty, returns an empty array.
     *
     * @return array The next message or an empty array if none available
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
     * Handle an incoming HTTP request.
     *
     * Processes the request and generates an appropriate response.
     * The request is forwarded to the MCP server for processing,
     * and the result is returned as a JSON response.
     *
     * @param ServerRequestInterface $request The incoming HTTP request
     * @return ResponseInterface The HTTP response
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET' && $request->getUri()->getPath() === '/health') {
            $response = json_encode(['status' => 'ok']);
            if ($response === false) {
                throw new \RuntimeException('Failed to encode health check response');
            }

            return new Response(200, [], $response);
        }

        if ($request->getMethod() === 'GET') {
            return new Response(200, [], 'OK');
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($request->getBody()->getContents(), true);
        if (! is_array($data)) {
            return new Response(400, [], 'Invalid JSON');
        }

        try {
            /** @var array<string, mixed> $response */
            $response = $this->processMessage($data);
            $encoded = json_encode($response);
            if ($encoded === false) {
                throw new \RuntimeException('Failed to encode response');
            }

            return new Response(200, [], $encoded);
        } catch (\RuntimeException $e) {
            return new Response(500, [], $e->getMessage());
        } catch (\Exception $e) {
            return new Response(400, [], $e->getMessage());
        }
    }

    /**
     * Process a message through the MCP server.
     *
     * Internal method that forwards a message to the MCP server
     * for processing and returns the result.
     *
     * @param array $data The message data to process
     * @return array The processing result
     */
    protected function processMessage(array $data): array
    {
        $type = $data['type'] ?? null;
        if ($type === null || ! is_string($type)) {
            throw new \RuntimeException('Unknown message type: ');
        }

        $name = $data['name'] ?? '';
        if (! is_string($name)) {
            throw new \RuntimeException('Invalid name type');
        }

        /** @var array<string, mixed> */
        $arguments = $data['arguments'] ?? [];
        if (! is_array($arguments)) {
            throw new \RuntimeException('Invalid arguments type');
        }

        $uri = $data['uri'] ?? '';
        if (! is_string($uri)) {
            throw new \RuntimeException('Invalid uri type');
        }

        /** @var array<string, mixed> */
        return match ($type) {
            'tool_call' => $this->mcpServer->handleToolCall(
                (string) $name,
                (array) $arguments
            ),
            'resource_request' => $this->mcpServer->handleResourceRequest(
                (string) $uri
            ),
            'prompt_request' => $this->mcpServer->handlePromptRequest(
                (string) $name,
                (array) $arguments
            ),
            default => throw new \RuntimeException('Unknown message type: ' . $type)
        };
    }
}
