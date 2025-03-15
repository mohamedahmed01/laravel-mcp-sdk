# HttpTransport

Namespace: ``

HTTP server-based transport implementation for the MCP system.

This transport creates an HTTP server that listens for incoming requests
and forwards them to the MCP server. It supports:
- HTTP/HTTPS communication
- Request/response handling
- Message queueing
- Multiple client connections

@package LaravelMCP\MCP\Transport

## Methods

### __construct

Create a new HTTP transport instance.

Initializes the HTTP server with the provided MCP server instance
and configuration. The server is configured to:
- Listen on the specified host and port
- Handle incoming HTTP requests
- Forward requests to the MCP server

@param MCPServerInterface $server The MCP server instance
@param array $config Configuration options:
                    - host: string - The host to listen on (default: 127.0.0.1)
                    - port: int - The port to listen on (default: 8080)

### start

Start the transport.

Starts the HTTP server, begins listening for connections,
and runs the event loop.

### stop

Stop the transport.

Stops the HTTP server, closes all connections,
and stops the event loop.

### send

Send data to connected clients.

Adds the data to the message queue for processing.
The data will be sent to clients on their next request.

@param array $data The data to send

### receive

Receive data from the message queue.

Returns and removes the next message from the queue.
If the queue is empty, returns an empty array.

@return array The next message or an empty array if none available

### isRunning

Check if the transport is running.

@return bool True if the transport is running, false otherwise

### handleRequest

Handle an incoming HTTP request.

Processes the request and generates an appropriate response.
The request is forwarded to the MCP server for processing,
and the result is returned as a JSON response.

@param ServerRequestInterface $request The incoming HTTP request
@return ResponseInterface The HTTP response

### processMessage

Process a message through the MCP server.

Internal method that forwards a message to the MCP server
for processing and returns the result.

@param array $data The message data to process
@return array The processing result

