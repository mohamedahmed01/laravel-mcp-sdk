<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use LaravelMCP\MCP\MCPClient;
use Tests\TestCase;

class MCPClientTest extends TestCase
{
    protected MCPClient $client;
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up default configuration
        config([
            'mcp.base_url' => 'http://example.com',
            'mcp.api_key' => 'test-api-key',
        ]);

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $httpClient = new Client(['handler' => $handlerStack]);
        $this->client = new MCPClient();

        // Set the client using reflection
        $reflection = new \ReflectionClass($this->client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($this->client, $httpClient);
    }

    public function testConstructorValidatesConfig(): void
    {
        config(['mcp.base_url' => null]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MCP base URL must be a string');
        new MCPClient();
    }

    public function testConstructorValidatesApiKey(): void
    {
        config(['mcp.api_key' => null]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('MCP API key must be a string');
        new MCPClient();
    }

    public function testCreateContext(): void
    {
        $responseData = ['id' => '123', 'name' => 'test context'];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->createContext(['name' => 'test context']);
        $this->assertEquals(['id' => '123', 'name' => 'test context'], $result);
    }

    public function testCreateContextWithEmptyData(): void
    {
        $responseData = ['id' => '123'];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->createContext([]);
        $this->assertEquals(['id' => '123'], $result);
    }

    public function testGetContext(): void
    {
        $responseData = ['id' => '123', 'name' => 'test context'];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->getContext('123');
        $this->assertEquals(['id' => '123', 'name' => 'test context'], $result);
    }

    public function testGetContextWithInvalidId(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(404, ['error' => 'Context not found'])
        );

        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $this->client->getContext('invalid-id');
    }

    public function testUpdateContext(): void
    {
        $responseData = ['id' => '123', 'name' => 'updated context'];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->updateContext('123', ['name' => 'updated context']);
        $this->assertEquals(['id' => '123', 'name' => 'updated context'], $result);
    }

    public function testUpdateContextWithInvalidData(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(400, ['error' => 'Invalid data'])
        );

        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $this->client->updateContext('123', ['invalid' => 'data']);
    }

    public function testDeleteContext(): void
    {
        $responseData = ['success' => true];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->deleteContext('123');
        $this->assertEquals(['success' => true], $result);
    }

    public function testDeleteContextWithServerError(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(500, ['error' => 'Server error'])
        );

        $this->expectException(\GuzzleHttp\Exception\ServerException::class);
        $this->client->deleteContext('123');
    }

    public function testListContexts(): void
    {
        $responseData = [
            'contexts' => [
                ['id' => '123', 'name' => 'context 1'],
                ['id' => '456', 'name' => 'context 2'],
            ],
        ];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->listContexts();
        $this->assertEquals($responseData, $result);
    }

    public function testListContextsWithParams(): void
    {
        $responseData = [
            'contexts' => [
                ['id' => '123', 'name' => 'context 1'],
            ],
            'pagination' => [
                'page' => 1,
                'per_page' => 1,
                'total' => 2,
            ],
        ];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->listContexts(['page' => 1, 'per_page' => 1]);
        $this->assertEquals($responseData, $result);
    }

    public function testListContextsWithInvalidResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
        );

        $this->expectException(\JsonException::class);
        $this->client->listContexts();
    }

    public function testListContextsWithNonArrayResponse(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(200, 'string response')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');
        $this->client->listContexts();
    }

    public function testListContextsWithPagination(): void
    {
        $responseData = [
            'data' => [
                ['id' => '1', 'name' => 'Context 1'],
                ['id' => '2', 'name' => 'Context 2'],
            ],
            'meta' => [
                'current_page' => 1,
                'total_pages' => 2,
            ],
        ];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->listContexts(['page' => 1, 'per_page' => 2]);
        $this->assertEquals($responseData, $result);
    }

    public function testCreateContextWithNullResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'null')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');
        $this->client->createContext(['name' => 'test']);
    }

    public function testGetContextWithEmptyResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, [], '')
        );

        $this->expectException(\JsonException::class);
        $this->client->getContext('123');
    }

    public function testUpdateContextWithBooleanResponse(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(200, true)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');
        $this->client->updateContext('123', ['name' => 'test']);
    }

    public function testInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(200, [], 'invalid json')
        );

        $this->expectException(\JsonException::class);
        $this->client->getContext('123');
    }

    public function testNonArrayResponse(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(200, 'string response')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');
        $this->client->getContext('123');
    }

    public function testConstructorWithCustomClient(): void
    {
        $httpClient = new Client(['handler' => HandlerStack::create(new MockHandler())]);
        $client = new MCPClient();

        // Set the client using reflection
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($client, $httpClient);

        $this->assertInstanceOf(MCPClient::class, $client);
    }

    public function testConstructorWithValidConfig(): void
    {
        config([
            'mcp.base_url' => 'http://example.com',
            'mcp.api_key' => 'test-api-key',
        ]);

        $client = new MCPClient();
        $this->assertInstanceOf(MCPClient::class, $client);

        // Test that the client was initialized with correct configuration
        $reflection = new \ReflectionClass($client);

        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertEquals('http://example.com', $baseUrlProperty->getValue($client));

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('test-api-key', $apiKeyProperty->getValue($client));

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $guzzleClient = $clientProperty->getValue($client);
        $this->assertInstanceOf(Client::class, $guzzleClient);
    }

    public function testConstructorWithInvalidBaseUrl(): void
    {
        config([
            'mcp.base_url' => 'invalid://url',
            'mcp.api_key' => 'test-api-key',
        ]);

        $client = new MCPClient();
        $this->assertInstanceOf(MCPClient::class, $client);

        // Test that the client was initialized with the invalid URL
        $reflection = new \ReflectionClass($client);

        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertEquals('invalid://url', $baseUrlProperty->getValue($client));

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('test-api-key', $apiKeyProperty->getValue($client));

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $guzzleClient = $clientProperty->getValue($client);
        $this->assertInstanceOf(Client::class, $guzzleClient);
    }

    public function testListContextsWithEmptyParams(): void
    {
        $responseData = [
            'contexts' => [
                ['id' => '123', 'name' => 'context 1'],
                ['id' => '456', 'name' => 'context 2'],
            ],
        ];
        $this->mockHandler->append(
            $this->createJsonResponse(200, $responseData)
        );

        $result = $this->client->listContexts([]);
        $this->assertEquals($responseData, $result);
    }

    public function testListContextsWithError(): void
    {
        $errorData = ['error' => 'Internal server error'];
        $this->mockHandler->append(
            $this->createJsonResponse(500, $errorData)
        );

        $this->expectException(\GuzzleHttp\Exception\ServerException::class);
        $this->client->listContexts();
    }

    public function testConstructorCreatesClient(): void
    {
        $client = new MCPClient();

        $reflection = new \ReflectionClass($client);

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $this->assertInstanceOf(Client::class, $clientProperty->getValue($client));

        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertEquals('http://example.com', $baseUrlProperty->getValue($client));

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('test-api-key', $apiKeyProperty->getValue($client));
    }

    public function testListContextsWithNonArrayJsonResponse(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(200, 42)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');
        $this->client->listContexts();
    }

    public function testHandlesJsonEncodingError(): void
    {
        // Create an invalid UTF-8 sequence that will cause JSON encoding to fail
        $invalidUtf8 = "\xFF\xFF\xFF\xFF";

        $this->mockHandler->append(
            new Response(200, [], (string)$invalidUtf8)
        );

        $this->expectException(\JsonException::class);
        $this->client->listContexts();
    }

    public function testConstructorAcceptsEmptyStrings(): void
    {
        config([
            'mcp.base_url' => '',
            'mcp.api_key' => '',
        ]);

        $client = new MCPClient();

        $reflection = new \ReflectionClass($client);

        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        $this->assertEquals('', $baseUrlProperty->getValue($client));

        $apiKeyProperty = $reflection->getProperty('apiKey');
        $apiKeyProperty->setAccessible(true);
        $this->assertEquals('', $apiKeyProperty->getValue($client));
    }

    public function testConstructorWithMissingConfig(): void
    {
        config()->set('mcp', null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MCP base URL must be a string');

        new MCPClient();
    }

    public function testDeleteContextWithNonArrayResponse(): void
    {
        $this->mockHandler->append(
            $this->createJsonResponse(200, 42)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected array response from MCP API');

        $this->client->deleteContext('123');
    }

    private function createJsonResponse(int $status, mixed $data): Response
    {
        $json = json_encode($data);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode response data');
        }

        return new Response($status, [], $json);
    }
}
