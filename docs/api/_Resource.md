# Resource

Namespace: ``

Implementation of a resource in the MCP system.

A resource represents a piece of content that can be accessed through the
MCP server. Resources can be static files, dynamic content, or data that
is generated on demand. Each resource has a unique URI, content handler,
and optional metadata like MIME type and description.

Resources can be used to:
- Serve static files
- Generate dynamic content
- Provide access to data stores
- Implement API endpoints

@package LaravelMCP\MCP\Server

## Methods

### __construct

Create a new resource instance.

@param string $uri The unique URI identifier for the resource
@param callable $handler The function that provides the resource's content
@param string|null $mimeType Optional MIME type of the content
@param string|null $description Optional description of the resource

### getUri

Get the resource's URI.

The URI uniquely identifies this resource within the MCP server.
It is used for routing requests and accessing the resource.

@return string The resource's URI

### getContent

Get the resource's content.

Retrieves the content by invoking the resource's handler function.
The content can be any type that the handler returns.

@return mixed The resource's content

### getMimeType

Get the resource's MIME type.

The MIME type indicates the format of the resource's content.
This is useful for HTTP responses and content negotiation.

@return string|null The MIME type, or null if not specified

### getDescription

Get the resource's description.

The description provides information about the resource's purpose,
usage, and any special considerations.

@return string|null The description, or null if not specified

### handle

Handle a request to this resource.

This is the main entry point for accessing the resource's content.
It invokes the handler function and returns its result.
Unlike getContent(), this method is intended for request handling
and may include additional processing in the future.

@return mixed The result of handling the request

### getName

Get the resource's name.

The name is derived from the resource's URI and uniquely identifies
the resource within the MCP server.

@return string The resource's name (same as its URI)

