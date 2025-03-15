<?php

namespace LaravelMCP\MCP\Commands;

use Exception;
use Illuminate\Console\Command;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\TransportFactory;
use React\EventLoop\LoopInterface;

/**
 * Artisan command for managing the MCP server.
 *
 * This command provides a CLI interface for starting, stopping, and managing
 * the MCP server within a Laravel application. It handles server initialization,
 * transport setup, and event loop management.
 *
 * Usage:
 * ```bash
 * # Start with default settings (HTTP transport)
 * php artisan mcp:serve
 *
 * # Start with custom transport and host
 * php artisan mcp:serve --transport=websocket --host=0.0.0.0
 *
 * # Start with custom port
 * php artisan mcp:serve --port=9000
 * ```
 *
 * Features:
 * - Multiple transport options (HTTP, WebSocket, stdio)
 * - Configurable host and port bindings
 * - Signal handling for graceful shutdown
 * - Event loop integration
 * - Error handling and reporting
 *
 * @package LaravelMCP\MCP\Commands
 */
class MCPServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     * --transport: The transport protocol to use
     *   - http: HTTP server (default)
     *   - websocket: WebSocket server
     *   - stdio: Standard I/O
     * --host: The host address to bind to (default: 127.0.0.1)
     * --port: The port number to listen on (default: 8080)
     *
     * @var string
     */
    protected $signature = 'mcp:serve {--transport=http : The transport to use (stdio, http)} {--host=127.0.0.1 : Host to bind to} {--port=8080 : Port to listen on}';

    /**
     * The console command description.
     *
     * Provides a brief overview of the command's purpose for the
     * php artisan list command.
     *
     * @var string
     */
    protected $description = 'Start the MCP server';

    /**
     * @var MCPServerInterface The MCP server instance
     */
    private MCPServerInterface $server;

    /**
     * @var TransportFactory Factory for creating transport instances
     */
    private TransportFactory $transportFactory;

    /**
     * @var LoopInterface The event loop instance
     */
    private LoopInterface $loop;

    /**
     * Create a new command instance.
     *
     * Initializes the command with its dependencies:
     * - MCP server for handling requests
     * - Transport factory for creating communication layers
     * - Event loop for asynchronous operations
     *
     * @param MCPServerInterface $server The MCP server instance
     * @param TransportFactory $transportFactory Factory for creating transports
     * @param LoopInterface $loop The event loop instance
     */
    public function __construct(
        MCPServerInterface $server,
        TransportFactory $transportFactory,
        LoopInterface $loop
    ) {
        parent::__construct();

        $this->server = $server;
        $this->transportFactory = $transportFactory;
        $this->loop = $loop;
    }

    /**
     * Execute the console command.
     *
     * This method:
     * 1. Validates command options
     * 2. Creates and configures the transport
     * 3. Initializes the server
     * 4. Sets up signal handlers (if available)
     * 5. Starts the event loop
     *
     * Signal handling (POSIX systems only):
     * - SIGINT (Ctrl+C): Graceful shutdown
     * - SIGTERM: Graceful shutdown
     *
     * Error handling:
     * - Invalid transport type
     * - Invalid host address
     * - Invalid port number
     * - Server initialization failures
     *
     * @return int 0 on success, 1 on failure
     */
    public function handle(): int
    {
        try {
            $transport = $this->option('transport');
            $host = $this->option('host');
            $port = $this->option('port');

            // Validate transport
            if (! is_string($transport)) {
                $this->error('Transport must be a string');

                return 1;
            }

            // Validate host
            if (! is_string($host)) {
                $this->error('Host must be a string');

                return 1;
            }

            // Validate port
            if (! is_string($port)) {
                $this->error('Port must be a string');

                return 1;
            }

            if (! ctype_digit($port)) {
                $this->error('Port must be a valid number');

                return 1;
            }
            $port = (int) $port;

            $transportInstance = $this->transportFactory->create(
                $transport,
                $this->server,
                $this->loop,
                [
                    'host' => $host,
                    'port' => $port,
                ]
            );

            $this->server->setTransport($transportInstance);
            $this->server->start();

            if (extension_loaded('pcntl')) {
                pcntl_signal(SIGINT, function () use ($transportInstance) {
                    $transportInstance->stop();
                    exit(0);
                });
                pcntl_signal(SIGTERM, function () use ($transportInstance) {
                    $transportInstance->stop();
                    exit(0);
                });
            }

            $this->loop->run();

            return 0;
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }
    }
}
