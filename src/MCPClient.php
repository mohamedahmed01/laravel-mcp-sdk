<?php

namespace LaravelMCP\MCP;

use GuzzleHttp\Client;
use InvalidArgumentException;
use LaravelMCP\MCP\Contracts\NotificationInterface;
use LaravelMCP\MCP\Contracts\RequestInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use LaravelMCP\MCP\Sampling\ModelPreferences;
use LaravelMCP\MCP\Transport\HttpClientTransport;
use RuntimeException;

/**
 * Client implementation for interacting with the MCP system.
 *
 * The MCPClient provides a high-level interface for applications to interact
 * with the MCP server. It handles communication through the transport layer,
 * manages requests and notifications, and provides convenience methods for
 * common operations.
 *
 * The client supports:
 * - Sending requests to the server
 * - Handling notifications
 * - Managing model preferences
 * - Configuring transport options
 *
 * @package LaravelMCP\MCP
 */
class MCPClient
{
    /**
     * @var TransportInterface
     */
    private TransportInterface $transport;

    /**
     * @var ModelPreferences|null Current model preferences
     */
    private ?ModelPreferences $preferences = null;

    private ?string $baseUrl = null;
    private ?string $apiKey = null;
    private Client $client;

    /**
     * Create a new MCP client instance.
     *
     * @param TransportInterface|null $transport
     * @param string|null $baseUrl
     * @param string|null $apiKey
     * @throws InvalidArgumentException
     */
    public function __construct(?TransportInterface $transport = null, ?string $baseUrl = null, ?string $apiKey = null)
    {
        $this->baseUrl = $baseUrl ?? config('mcp.base_url');
        $this->apiKey = $apiKey ?? config('mcp.api_key');

        if ($this->baseUrl === null) {
            throw new InvalidArgumentException('MCP base URL must be a string');
        }

        if ($this->apiKey === null) {
            throw new InvalidArgumentException('MCP API key must be a string');
        }

        if (! is_string($this->baseUrl)) {
            throw new InvalidArgumentException('MCP base URL must be a string');
        }

        if (! is_string($this->apiKey)) {
            throw new InvalidArgumentException('MCP API key must be a string');
        }

        $this->transport = $transport ?? new HttpClientTransport($this->baseUrl, $this->apiKey);
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Send a request to the MCP server.
     *
     * Processes the request through the transport layer and returns
     * the server's response.
     *
     * @param RequestInterface $request The request to send
     * @return mixed The server's response
     */
    public function sendRequest(RequestInterface $request): mixed
    {
        $this->transport->send([
            'type' => $request->getType(),
            'arguments' => $request->getArguments(),
        ]);

        return true; // Return a value since we're using the result
    }

    /**
     * Send a notification to the MCP server.
     *
     * Sends a one-way notification through the transport layer.
     * No response is expected.
     *
     * @param NotificationInterface $notification The notification to send
     */
    public function sendNotification(NotificationInterface $notification): void
    {
        $this->transport->send([
            'type' => $notification->getType(),
            'data' => $notification->getData(),
        ]);
    }

    /**
     * Set model preferences for the client.
     *
     * These preferences will be used for subsequent requests that
     * involve model sampling.
     *
     * @param ModelPreferences $preferences The preferences to set
     */
    public function setModelPreferences(ModelPreferences $preferences): void
    {
        $this->preferences = $preferences;
    }

    /**
     * Get the current model preferences.
     *
     * @return ModelPreferences|null The current preferences or null if not set
     */
    public function getModelPreferences(): ?ModelPreferences
    {
        return $this->preferences;
    }

    /**
     * Get the transport layer instance.
     *
     * @return TransportInterface The transport layer
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * Start the client's transport layer.
     *
     * This initializes the transport and prepares it for
     * sending requests and notifications.
     */
    public function start(): void
    {
        $this->transport->start();
    }

    /**
     * Stop the client's transport layer.
     *
     * This gracefully shuts down the transport and cleans up
     * any resources.
     */
    public function stop(): void
    {
        $this->transport->stop();
    }

    /**
     * Create a new model context.
     *
     * Creates a new context on the MCP server with the provided data.
     * The context can be used to maintain state between model interactions.
     *
     * @param array $data The data to initialize the context with
     * @return array The created context data
     * @throws \JsonException When JSON decoding fails
     * @throws RuntimeException When the API response is not an array
     * @throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
     */
    public function createContext(array $data): array
    {
        $response = $this->client->post('/contexts', [
            'json' => $data,
        ]);

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result)) {
            throw new \RuntimeException('Expected array response from MCP API');
        }

        return $result;
    }

    /**
     * Get a model context by ID.
     *
     * Retrieves an existing context from the MCP server using its unique identifier.
     *
     * @param string $contextId The unique identifier of the context to retrieve
     * @return array The context data
     * @throws \JsonException When JSON decoding fails
     * @throws RuntimeException When the API response is not an array
     * @throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
     */
    public function getContext(string $contextId): array
    {
        $response = $this->client->get("/contexts/{$contextId}");

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result)) {
            throw new \RuntimeException('Expected array response from MCP API');
        }

        return $result;
    }

    /**
     * Update a model context.
     *
     * Updates an existing context on the MCP server with new data.
     * Only the provided fields will be updated.
     *
     * @param string $contextId The unique identifier of the context to update
     * @param array $data The new data to update the context with
     * @return array The updated context data
     * @throws \JsonException When JSON decoding fails
     * @throws RuntimeException When the API response is not an array
     * @throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
     */
    public function updateContext(string $contextId, array $data): array
    {
        $response = $this->client->put("/contexts/{$contextId}", [
            'json' => $data,
        ]);

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result)) {
            throw new \RuntimeException('Expected array response from MCP API');
        }

        return $result;
    }

    /**
     * Delete a model context.
     *
     * Permanently removes a context from the MCP server.
     * This action cannot be undone.
     *
     * @param string $contextId The unique identifier of the context to delete
     * @return array The deletion response data
     * @throws \JsonException When JSON decoding fails
     * @throws RuntimeException When the API response is not an array
     * @throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
     */
    public function deleteContext(string $contextId): array
    {
        $response = $this->client->delete("/contexts/{$contextId}");

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result)) {
            throw new \RuntimeException('Expected array response from MCP API');
        }

        return $result;
    }

    /**
     * List all model contexts.
     *
     * Retrieves a list of all contexts from the MCP server.
     * The list can be filtered and paginated using query parameters.
     *
     * @param array $params Optional query parameters for filtering and pagination
     *                     Supported parameters:
     *                     - page: int - The page number to retrieve
     *                     - per_page: int - Number of items per page
     *                     - sort: string - Field to sort by
     *                     - order: string - Sort order (asc/desc)
     * @return array The list of contexts and pagination metadata
     * @throws \JsonException When JSON decoding fails
     * @throws RuntimeException When the API response is not an array
     * @throws \GuzzleHttp\Exception\GuzzleException When the HTTP request fails
     */
    public function listContexts(array $params = []): array
    {
        $response = $this->client->get('/contexts', [
            'query' => $params,
        ]);

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($result)) {
            throw new \RuntimeException('Expected array response from MCP API');
        }

        return $result;
    }

    /**
     * Send data to a specific URI.
     *
     * @param string $uri The URI to send to
     * @param array $data The data to send
     */
    public function send(string $uri, array $data = []): void
    {
        $this->transport->send([
            'uri' => $uri,
            'data' => $data,
        ]);
    }
}
