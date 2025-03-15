<?php

namespace Tests\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\WebSocketTransport;
use Mockery;
use Mockery\MockInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Loop;
use Tests\TestCase;

class WebSocketTransportTest extends TestCase
{
    /** @var MockInterface&MCPServerInterface */
    protected $server;

    protected WebSocketTransport $transport;

    /** @var MockInterface&ConnectionInterface */
    protected $connection;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&MCPServerInterface $server */
        $server = Mockery::mock(MCPServerInterface::class);
        $this->server = $server;

        $loop = Loop::get();
        $this->transport = new WebSocketTransport($loop, $this->server, [
            'host' => '127.0.0.1',
            'port' => 8080,
        ]);

        /** @var MockInterface&ConnectionInterface $connection */
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->resourceId = 1;
        $this->connection = $connection;
    }

    public function testConstructorWithDefaultConfig(): void
    {
        $loop = Loop::get();
        $transport = new WebSocketTransport($loop, $this->server);
        $this->assertInstanceOf(WebSocketTransport::class, $transport);
    }

    public function testConstructorWithCustomConfig(): void
    {
        $loop = Loop::get();
        $transport = new WebSocketTransport($loop, $this->server, [
            'host' => '0.0.0.0',
            'port' => 9090,
        ]);
        $this->assertInstanceOf(WebSocketTransport::class, $transport);
    }

    public function testSendAndReceive(): void
    {
        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"type":"test","data":"value"}');

        $this->transport->onOpen($this->connection);

        $data = ['type' => 'test', 'data' => 'value'];
        $this->transport->send($data);

        $received = $this->transport->receive();
        $this->assertEquals($data, $received);

        // Queue should be empty after receiving
        $this->assertEquals([], $this->transport->receive());
    }

    public function testRunningState(): void
    {
        $this->assertFalse($this->transport->isRunning());

        $this->transport->start();
        $this->assertTrue($this->transport->isRunning());

        $this->transport->stop();
        $this->assertFalse($this->transport->isRunning());
    }

    public function testConnectionLifecycle(): void
    {
        // Test connection open
        $this->transport->onOpen($this->connection);

        // Send message to verify connection is active
        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"type":"test"}');
        $this->transport->send(['type' => 'test']);

        // Test connection close
        $this->transport->onClose($this->connection);

        // Send message to verify connection is closed (should not receive)
        $this->transport->send(['type' => 'test2']);
    }

    public function testOnError(): void
    {
        $this->connection->shouldReceive('close')->once();
        $this->transport->onError($this->connection, new \Exception('Test error'));
    }

    public function testOnMessageWithInvalidJson(): void
    {
        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"error":"Invalid JSON"}');
        $this->transport->onMessage($this->connection, 'invalid json');
    }

    public function testHandleToolCallMessage(): void
    {
        $this->server->shouldReceive('handleToolCall')
            ->once()
            ->with('test_tool', ['arg' => 'value'])
            ->andReturn(['result' => 'success']);

        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"result":"success"}');

        $message = json_encode([
            'type' => 'tool_call',
            'name' => 'test_tool',
            'arguments' => ['arg' => 'value'],
        ]);

        if ($message === false) {
            $this->fail('Failed to encode message');
        }

        $this->transport->onMessage($this->connection, $message);
    }

    public function testHandleResourceRequest(): void
    {
        $this->server->shouldReceive('handleResourceRequest')
            ->once()
            ->with('test://resource')
            ->andReturn(['resource' => 'data']);

        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"resource":"data"}');

        $message = json_encode([
            'type' => 'resource_request',
            'uri' => 'test://resource',
        ]);

        if ($message === false) {
            $this->fail('Failed to encode message');
        }

        $this->transport->onMessage($this->connection, $message);
    }

    public function testHandlePromptRequest(): void
    {
        $this->server->shouldReceive('handlePromptRequest')
            ->once()
            ->with('test_prompt', ['arg' => 'value'])
            ->andReturn(['prompt' => 'result']);

        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"prompt":"result"}');

        $message = json_encode([
            'type' => 'prompt_request',
            'name' => 'test_prompt',
            'arguments' => ['arg' => 'value'],
        ]);

        if ($message === false) {
            $this->fail('Failed to encode message');
        }

        $this->transport->onMessage($this->connection, $message);
    }

    public function testHandleUnknownMessageType(): void
    {
        $this->connection->shouldReceive('send')
            ->once()
            ->with('{"error":"Unknown message type: unknown_type"}');

        $message = json_encode([
            'type' => 'unknown_type',
        ]);

        if ($message === false) {
            $this->fail('Failed to encode message');
        }

        $this->transport->onMessage($this->connection, $message);
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
        if ($this->transport->isRunning()) {
            $this->transport->stop();
        }
        Mockery::close();
    }
}
