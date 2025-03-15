# MCPServerCommand

Namespace: ``

Artisan command for managing the MCP server.

This command provides a CLI interface for starting, stopping, and managing
the MCP server within a Laravel application. It handles server initialization,
transport setup, and event loop management.

Usage:
```bash
# Start with default settings (HTTP transport)
php artisan mcp:serve

# Start with custom transport and host
php artisan mcp:serve --transport=websocket --host=0.0.0.0

# Start with custom port
php artisan mcp:serve --port=9000
```

Features:
- Multiple transport options (HTTP, WebSocket, stdio)
- Configurable host and port bindings
- Signal handling for graceful shutdown
- Event loop integration
- Error handling and reporting

@package LaravelMCP\MCP\Commands

## Methods

### __construct

Create a new command instance.

Initializes the command with its dependencies:
- MCP server for handling requests
- Transport factory for creating communication layers
- Event loop for asynchronous operations

@param MCPServerInterface $server The MCP server instance
@param TransportFactory $transportFactory Factory for creating transports
@param LoopInterface $loop The event loop instance

### handle

Execute the console command.

This method:
1. Validates command options
2. Creates and configures the transport
3. Initializes the server
4. Sets up signal handlers (if available)
5. Starts the event loop

Signal handling (POSIX systems only):
- SIGINT (Ctrl+C): Graceful shutdown
- SIGTERM: Graceful shutdown

Error handling:
- Invalid transport type
- Invalid host address
- Invalid port number
- Server initialization failures

@return int 0 on success, 1 on failure

