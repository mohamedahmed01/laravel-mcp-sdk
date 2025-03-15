# Tool

Namespace: ``

Implementation of a tool in the MCP system.

A tool represents a specific functionality that can be invoked by clients
through the MCP server. Each tool has a unique name, a handler function
that implements its logic, and optional configuration parameters.

Tools can be used to:
- Execute system commands
- Manipulate files and resources
- Process data
- Integrate with external services

@package LaravelMCP\MCP\Server

## Methods

### __construct

Create a new tool instance.

@param string $name The unique identifier for the tool
@param callable $handler The function that implements the tool's logic
@param string|null $description Optional description of the tool's purpose

### getName

{@inheritdoc}

### getHandler

{@inheritdoc}

### getDescription

{@inheritdoc}

### getParameters

{@inheritdoc}

### setParameters

{@inheritdoc}

### handle

Handle a request to execute this tool.

Processes the provided arguments using the tool's handler function
and returns the result. The handler's return value is automatically
converted to an array if it isn't one already.

@param array $arguments The arguments to pass to the handler
@return array The result of executing the tool

