<?php

namespace Tests\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\HttpTransport;
use LaravelMCP\MCP\Transport\StdioTransport;
use LaravelMCP\MCP\Transport\WebSocketTransport;
use Mockery;
use Mockery\MockInterface;
use React\EventLoop\Loop;
use Tests\TestCase;

class TransportTest extends TestCase
{
    /** @var MockInterface&MCPServerInterface */
    protected $server;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&MCPServerInterface $server */
        $server = Mockery::mock(MCPServerInterface::class);
        $this->server = $server;
    }

    public function testCreateHttpTransport(): void
    {
        $transport = new HttpTransport($this->server);
        $this->assertInstanceOf(HttpTransport::class, $transport);
    }

    public function testCreateWebSocketTransport(): void
    {
        $loop = Loop::get();
        $transport = new WebSocketTransport($loop, $this->server);
        $this->assertInstanceOf(WebSocketTransport::class, $transport);
    }

    public function testCreateStdioTransport(): void
    {
        $transport = new StdioTransport($this->server);
        $this->assertInstanceOf(StdioTransport::class, $transport);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
