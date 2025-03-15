# StdioTransport

Namespace: ``

Standard I/O transport implementation for the MCP system.

This transport provides communication between clients and the MCP server
through standard input/output streams. It's particularly useful for
command-line applications and testing. The transport uses React PHP's
event loop for asynchronous I/O operations.

Features:
- Non-blocking I/O
- JSON message encoding/decoding
- Event-driven processing
- Simple integration with CLI tools

@package LaravelMCP\MCP\Transport

## Methods

### __construct

Create a new standard I/O transport instance.

@param MCPServerInterface $server The MCP server instance

### start

Start the transport.

Sets up non-blocking I/O streams and starts the event loop to
process incoming messages from standard input.

### stop

Stop the transport.

Stops the event loop and marks the transport as not running.

### send

Send a message through the transport.

Encodes the message as JSON and writes it to standard output.

@param array $data The message data to send
@throws \RuntimeException If message encoding fails

### receive

Receive a message from the transport.

@return array The next message in the queue or an empty array if none

### isRunning

Check if the transport is running.

@return bool True if the transport is running, false otherwise

### onMessage

Handle an incoming message.

Processes the message by decoding it from JSON and passing it to
the message handler. Sends the handler's response back.

Error handling:
- Invalid JSON: Returns {"error": "Invalid JSON"}
- Processing error: Returns {"error": "error message"}

@param string $msg The raw message to process

### processMessage

Process a decoded message.

Routes the message to the appropriate handler based on its type
and returns the handler's response.

Supported message types:
- tool_call: Handles tool execution requests
- resource_request: Handles resource access requests
- prompt_request: Handles prompt generation requests

Message format:
{
    "type": string,      // Message type (required)
    "name": string,      // Tool/prompt name (optional)
    "arguments": object, // Parameters (optional)
    "uri": string       // Resource URI (optional)
}

@param array<string, mixed> $data The decoded message data
@return array<string, mixed> The handler's response
@throws \RuntimeException If the message type is unknown

