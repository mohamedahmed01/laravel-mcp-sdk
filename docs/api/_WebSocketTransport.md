# WebSocketTransport

Namespace: ``

WebSocket transport implementation for the MCP system.

This transport provides WebSocket-based communication between clients and
the MCP server. It sets up a WebSocket server that can handle real-time,
bidirectional communication. The transport uses Ratchet and React PHP for
asynchronous WebSocket operations.

Features:
- WebSocket protocol support
- Real-time bidirectional communication
- Message queueing
- Client connection management
- Asynchronous processing

@package LaravelMCP\MCP\Transport

## Methods

### __construct

Create a new WebSocket transport instance.

@param LoopInterface $loop The event loop to use
@param MCPServerInterface $server The MCP server instance
@param array $config Optional configuration parameters

### getHost

Get the server host address.

@return string The host address

### getPort

Get the server port number.

@return int The port number

### start

{@inheritdoc}

### stop

{@inheritdoc}

### send

Send data to all connected clients.

@param array $data The data to send

### receive

{@inheritdoc}

### isRunning

{@inheritdoc}

### onOpen

Handle a new WebSocket connection.

@param ConnectionInterface $conn The connection instance

### onClose

Handle a closed WebSocket connection.

@param ConnectionInterface $conn The connection instance

### onError

Handle a WebSocket connection error.

@param ConnectionInterface $conn The connection instance
@param \Exception $e The error that occurred

### onMessage

Handle an incoming WebSocket message.

@param ConnectionInterface $from The connection that sent the message
@param string $msg The message content

### processMessage

Process an incoming message from a client.

This method handles the core message processing logic:
1. Validates the message structure
2. Extracts type, name, and arguments
3. Forwards the message to the MCP server
4. Returns the processed result

Expected message format:
{
    "type": string,      // The message type (required)
    "name": string,      // Resource name (optional)
    "arguments": object, // Message arguments (optional)
    "uri": string       // Resource URI (optional)
}

@param array<string, mixed> $data The message data to process
@return array<string, mixed> The processing result

### setMessageHandler

Set a custom message handler function.

The handler will be called for each incoming message before standard processing.
It can be used to implement custom message handling logic or preprocessing.

The handler function should have the following signature:
function(array $message): ?array

If the handler returns null, standard message processing will continue.
If it returns an array, that array will be used as the response.

@param callable $handler The message handler function

### getMessageHandler

Get the current message handler function.

@return callable|null The current message handler or null if none set

