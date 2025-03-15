<?php

namespace Tests\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Stream;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\HttpTransport;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class HttpTransportTest extends TestCase
{
    /** @var MockInterface&MCPServerInterface */
    protected $server;

    protected HttpTransport $transport;
    protected MockHandler $mockHandler;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&MCPServerInterface $server */
        $server = Mockery::mock(MCPServerInterface::class);
        $this->server = $server;

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);

        $this->client = new Client(['handler' => $handlerStack]);
        $this->transport = new HttpTransport($this->server, ['client' => $this->client]);
    }

    public function testConstructorWithDefaultConfig(): void
    {
        $transport = new HttpTransport($this->server);
        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    public function testConstructorWithCustomClient(): void
    {
        $transport = new HttpTransport($this->server, ['client' => $this->client]);
        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    public function testSendAndReceive(): void
    {
        $responseData = ['result' => 'success'];
        $responseJson = json_encode($responseData);
        if ($responseJson === false) {
            $this->fail('Failed to encode response data');
        }
        $this->mockHandler->append(new Response(200, [], $responseJson));

        $data = ['type' => 'test', 'data' => 'value'];
        $this->transport->send($data);

        $received = $this->transport->receive();
        $this->assertEquals($responseData, $received);

        // Queue should be empty after receiving
        $this->assertEquals([], $this->transport->receive());
    }

    public function testHandleToolCall(): void
    {
        $this->server->shouldReceive('handleToolCall')
            ->once()
            ->with('test_tool', ['arg' => 'value'])
            ->andReturn(['result' => 'success']);

        $requestData = [
            'type' => 'tool_call',
            'name' => 'test_tool',
            'arguments' => ['arg' => 'value'],
        ];
        $requestJson = json_encode($requestData);
        if ($requestJson === false) {
            $this->fail('Failed to encode request data');
        }

        $resource = fopen('php://memory', 'r+');
        if ($resource === false) {
            $this->fail('Failed to open memory stream');
        }
        $stream = new Stream($resource);
        $stream->write($requestJson);
        $stream->rewind();

        $request = new ServerRequest('POST', '/', [], $stream);
        $response = $this->transport->handleRequest($request);
        $this->assertEquals(['result' => 'success'], json_decode((string) $response->getBody(), true));
    }

    public function testHandleResourceRequest(): void
    {
        $this->server->shouldReceive('handleResourceRequest')
            ->once()
            ->with('test://resource')
            ->andReturn(['resource' => 'data']);

        $requestData = [
            'type' => 'resource_request',
            'uri' => 'test://resource',
        ];
        $requestJson = json_encode($requestData);
        if ($requestJson === false) {
            $this->fail('Failed to encode request data');
        }

        $resource = fopen('php://memory', 'r+');
        if ($resource === false) {
            $this->fail('Failed to open memory stream');
        }
        $stream = new Stream($resource);
        $stream->write($requestJson);
        $stream->rewind();

        $request = new ServerRequest('POST', '/', [], $stream);
        $response = $this->transport->handleRequest($request);
        $this->assertEquals(['resource' => 'data'], json_decode((string) $response->getBody(), true));
    }

    public function testHandlePromptRequest(): void
    {
        $this->server->shouldReceive('handlePromptRequest')
            ->once()
            ->with('test_prompt', ['arg' => 'value'])
            ->andReturn(['prompt' => 'result']);

        $requestData = [
            'type' => 'prompt_request',
            'name' => 'test_prompt',
            'arguments' => ['arg' => 'value'],
        ];
        $requestJson = json_encode($requestData);
        if ($requestJson === false) {
            $this->fail('Failed to encode request data');
        }

        $resource = fopen('php://memory', 'r+');
        if ($resource === false) {
            $this->fail('Failed to open memory stream');
        }
        $stream = new Stream($resource);
        $stream->write($requestJson);
        $stream->rewind();

        $request = new ServerRequest('POST', '/', [], $stream);
        $response = $this->transport->handleRequest($request);
        $this->assertEquals(['prompt' => 'result'], json_decode((string) $response->getBody(), true));
    }

    public function testHandleUnknownMessageType(): void
    {
        $requestData = [
            'type' => 'unknown_type',
        ];
        $requestJson = json_encode($requestData);
        if ($requestJson === false) {
            $this->fail('Failed to encode request data');
        }

        $resource = fopen('php://memory', 'r+');
        if ($resource === false) {
            $this->fail('Failed to open memory stream');
        }
        $stream = new Stream($resource);
        $stream->write($requestJson);
        $stream->rewind();

        $request = new ServerRequest('POST', '/', [], $stream);
        $response = $this->transport->handleRequest($request);
        $this->assertEquals(
            ['error' => 'Unknown message type: unknown_type'],
            json_decode((string) $response->getBody(), true)
        );
    }

    public function testInvalidJsonResponse(): void
    {
        $this->mockHandler->append(new Response(200, [], 'invalid json'));

        $this->transport->send(['type' => 'test']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $this->transport->receive();
    }

    public function testNonArrayResponse(): void
    {
        $this->mockHandler->append(new Response(200, [], '"string response"'));

        $this->transport->send(['type' => 'test']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Response must be an array');

        $this->transport->receive();
    }

    public function testSendWithInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to encode message');

        $data = ['data' => fopen('php://memory', 'r')]; // Cannot be JSON encoded
        $this->transport->send($data);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
