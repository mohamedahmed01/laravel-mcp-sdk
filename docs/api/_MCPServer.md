# MCPServer

Namespace: ``

The main MCP (Model-Controller-Prompt) server implementation.

This class serves as the core of the MCP system, managing tools, resources,
prompts, and handling communication through the transport layer. It provides
a robust platform for building AI-powered applications with Laravel.

The server supports:
- Tool registration and execution
- Resource management and templating
- Prompt handling and processing
- Progress tracking and logging
- Model preference configuration

@package LaravelMCP\MCP\Server

## Methods

### __construct

Create a new MCP server instance.

### setTransport

Set the transport layer for the server.

@param TransportInterface $transport The transport to use

### getTransport

Get the current transport layer.

@return TransportInterface|null The current transport or null if not set

### initialize

{@inheritdoc}

### registerTool

{@inheritdoc}

### registerResource

{@inheritdoc}

### getCapabilities

{@inheritdoc}

### handleToolCall

{@inheritdoc}

### handleResourceRequest

{@inheritdoc}

### handlePromptRequest

{@inheritdoc}

### getTools

{@inheritdoc}

### getResources

{@inheritdoc}

### getResourceTemplates

{@inheritdoc}

### getPrompts

{@inheritdoc}

### getRoots

{@inheritdoc}

### addTool

Add a tool to the server.

@param string $name The unique identifier for the tool
@param callable $handler The function that implements the tool's logic
@param string|null $description Optional description of the tool's purpose

### addResource

Add a resource to the server.

@param string $uri The URI identifier for the resource
@param mixed $content The resource content
@param string|null $mimeType Optional MIME type of the resource
@param string|null $description Optional description of the resource
@throws \RuntimeException If a resource with the given URI already exists

### addResourceTemplate

Add a resource template to the server.

@param string $uri The URI for the resource template
@param ResourceTemplateInterface $template The resource template to add
@throws RuntimeException If a resource with the given URI already exists

### addPrompt

{@inheritdoc}

### addRoot

{@inheritdoc}

### handleCompletion

{@inheritdoc}

### sendProgress

{@inheritdoc}

### sendLog

{@inheritdoc}

### getModelPreferences

{@inheritdoc}

### setModelPreferences

{@inheritdoc}

### start

{@inheritdoc}

### stop

{@inheritdoc}

### addToolInterface

{@inheritdoc}

### addResourceInterface

{@inheritdoc}

### addPromptInterface

{@inheritdoc}

### getResource

Get a registered resource by URI.

@param string $uri The URI of the resource
@return ResourceInterface|null The resource or null if not found

### getPrompt

Get a registered prompt by name.

@param string $name The name of the prompt
@return PromptInterface|null The prompt or null if not found

### getHandler

Get the handler for a prompt.

@param string $name The name of the prompt
@return callable|null The handler or null if not found

### registerPrompt

Register a new prompt with the server.

@param string $name The name of the prompt
@param callable $handler The handler function for the prompt
@param array $arguments Optional arguments for the prompt
@return void

### hasResource

Check if a resource exists with the given URI.

@param string $uri The URI to check
@return bool True if the resource exists, false otherwise

### removeResource

Remove a resource with the given URI.

@param string $uri The URI of the resource to remove
@throws RuntimeException If the resource does not exist

