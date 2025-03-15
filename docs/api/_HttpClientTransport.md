# HttpClientTransport

Namespace: ``

HTTP client-based transport implementation for the MCP system.

This transport uses Guzzle HTTP client to communicate with the MCP server
over HTTP/HTTPS. It supports:
- Authenticated requests using API keys
- JSON-based message exchange
- Message queueing for asynchronous processing

@package LaravelMCP\MCP\Transport

## Methods

### __construct

Create a new HTTP client transport instance.

Initializes the Guzzle client with the provided base URL and API key.
The client is configured to:
- Use the base URL for all requests
- Include the API key in Authorization header
- Use JSON for request/response content

@param string $baseUrl The base URL of the MCP server
@param string $apiKey The API key for authentication

### start

Start the transport.

Marks the transport as running, enabling message sending and receiving.

### stop

Stop the transport.

Marks the transport as stopped, preventing further message processing.

### send

Send data to the MCP server.

Sends a POST request to the server's root endpoint with the provided
data as JSON. If the server responds with valid JSON, it is added
to the message queue for processing.

@param array $data The data to send to the server
@throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
@throws \JsonException When JSON decoding fails

### receive

Receive data from the message queue.

Returns and removes the next message from the queue.
If the queue is empty, returns an empty array.

@return array The next message or an empty array if none available

### isRunning

Check if the transport is running.

@return bool True if the transport is running, false otherwise

