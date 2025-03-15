# TransportFactory

Namespace: ``

Factory for creating transport instances in the MCP system.

This factory is responsible for creating and configuring transport
implementations based on the requested type. It supports multiple
transport protocols and handles their initialization with the
appropriate event loop.

Supported transports:
- HTTP
- WebSocket
- Standard I/O

@package LaravelMCP\MCP\Transport

## Methods

### create

Create a new transport instance.

Creates and configures a transport implementation based on the
specified type. The transport will be initialized with the
provided server, event loop, and configuration.

@param string $type The type of transport to create ('http', 'websocket', 'stdio')
@param MCPServerInterface $server The MCP server instance
@param LoopInterface $loop The event loop to use
@param array $config Optional configuration parameters
@return TransportInterface The configured transport instance
@throws RuntimeException If the transport type is not supported

