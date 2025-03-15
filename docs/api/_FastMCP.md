# FastMCP

Namespace: ``

A fast implementation of the MCP server interface.

This class provides a lightweight and efficient implementation of the MCP server
with support for tools, resources, prompts, and lifecycle management.

@package LaravelMCP\MCP\Server

## Methods

### __construct

Create a new FastMCP instance.

@param MCPServerInterface|null $server Optional server instance to use

### tool

Register a tool with the server.

@param string $name Tool name
@param array $parameters Tool parameters
@return Closure Registration handler

### resource

Register a resource with the server.

@param string $uri Resource URI
@return Closure Registration handler

### prompt

Register a prompt with the server.

@param string $name Prompt name
@param array $arguments Prompt arguments
@return Closure Registration handler

### lifespan

Set the server lifecycle handler.

@param Closure $handler Lifecycle handler function

### getServer

Get the underlying server instance.

@return MCPServerInterface

### getDependencies

Get the list of server dependencies.

@return array

### getLifespan

Get the server lifecycle handler.

@return Closure|null

### handleCompletion

Handle completion requests.

@param array $arguments Completion arguments
@param array $messages Message history
@return array Completion response

