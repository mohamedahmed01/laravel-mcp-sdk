# MCPClient

Namespace: ``

Client implementation for interacting with the MCP system.

The MCPClient provides a high-level interface for applications to interact
with the MCP server. It handles communication through the transport layer,
manages requests and notifications, and provides convenience methods for
common operations.

The client supports:
- Sending requests to the server
- Handling notifications
- Managing model preferences
- Configuring transport options

@package LaravelMCP\MCP

## Methods

### __construct

Create a new MCP client instance.

@param TransportInterface|null $transport
@param string|null $baseUrl
@param string|null $apiKey
@throws InvalidArgumentException

### sendRequest

Send a request to the MCP server.

Processes the request through the transport layer and returns
the server's response.

@param RequestInterface $request The request to send
@return mixed The server's response

### sendNotification

Send a notification to the MCP server.

Sends a one-way notification through the transport layer.
No response is expected.

@param NotificationInterface $notification The notification to send

### setModelPreferences

Set model preferences for the client.

These preferences will be used for subsequent requests that
involve model sampling.

@param ModelPreferences $preferences The preferences to set

### getModelPreferences

Get the current model preferences.

@return ModelPreferences|null The current preferences or null if not set

### getTransport

Get the transport layer instance.

@return TransportInterface The transport layer

### start

Start the client's transport layer.

This initializes the transport and prepares it for
sending requests and notifications.

### stop

Stop the client's transport layer.

This gracefully shuts down the transport and cleans up
any resources.

### createContext

Create a new model context.

Creates a new context on the MCP server with the provided data.
The context can be used to maintain state between model interactions.

@param array $data The data to initialize the context with
@return array The created context data
@throws \JsonException When JSON decoding fails
@throws RuntimeException When the API response is not an array
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails

### getContext

Get a model context by ID.

Retrieves an existing context from the MCP server using its unique identifier.

@param string $contextId The unique identifier of the context to retrieve
@return array The context data
@throws \JsonException When JSON decoding fails
@throws RuntimeException When the API response is not an array
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails

### updateContext

Update a model context.

Updates an existing context on the MCP server with new data.
Only the provided fields will be updated.

@param string $contextId The unique identifier of the context to update
@param array $data The new data to update the context with
@return array The updated context data
@throws \JsonException When JSON decoding fails
@throws RuntimeException When the API response is not an array
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails

### deleteContext

Delete a model context.

Permanently removes a context from the MCP server.
This action cannot be undone.

@param string $contextId The unique identifier of the context to delete
@return array The deletion response data
@throws \JsonException When JSON decoding fails
@throws RuntimeException When the API response is not an array
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails

### listContexts

List all model contexts.

Retrieves a list of all contexts from the MCP server.
The list can be filtered and paginated using query parameters.

@param array $params Optional query parameters for filtering and pagination
                    Supported parameters:
                    - page: int - The page number to retrieve
                    - per_page: int - Number of items per page
                    - sort: string - Field to sort by
                    - order: string - Sort order (asc/desc)
@return array The list of contexts and pagination metadata
@throws \JsonException When JSON decoding fails
@throws RuntimeException When the API response is not an array
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails

### send

Send data to a specific URI.

@param string $uri The URI to send to
@param array $data The data to send

