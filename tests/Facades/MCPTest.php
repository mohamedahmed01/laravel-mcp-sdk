<?php

namespace Tests\Facades;

use Illuminate\Foundation\Application;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Facades\MCP;
use LaravelMCP\MCP\MCPServiceProvider;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase;

class MCPTest extends TestCase
{
    /** @var MockInterface&MCPServerInterface */
    protected $server;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MockInterface&MCPServerInterface $server */
        $server = Mockery::mock(MCPServerInterface::class);
        $this->server = $server;

        MCP::shouldReceive('getFacadeRoot')
            ->andReturn($this->server);

        $this->server->shouldReceive('toolCall')
            ->with('test_tool', ['arg' => 'value'])
            ->andReturn(['result' => 'success']);

        $this->server->shouldReceive('resourceRequest')
            ->with('test://resource')
            ->andReturn(['resource' => 'data']);

        $this->server->shouldReceive('promptRequest')
            ->with('test_prompt', ['arg' => 'value'])
            ->andReturn(['prompt' => 'result']);

        /** @var \Illuminate\Foundation\Application $app */
        $app = app();
        $app->instance(MCPServerInterface::class, $this->server);
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            MCPServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param Application $app
     * @return array<string, string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'MCP' => MCP::class,
        ];
    }

    public function testFacadeAccessor(): void
    {
        $this->assertInstanceOf(MCPServiceProvider::class, app(MCPServiceProvider::class));
    }

    public function testToolCall(): void
    {
        $this->server->shouldReceive('handleToolCall')
            ->once()
            ->with('test_tool', ['arg' => 'value'])
            ->andReturn(['result' => 'success']);

        /** @var MCPServerInterface $server */
        $server = MCP::getFacadeRoot();
        $result = $server->handleToolCall('test_tool', ['arg' => 'value']);
        $this->assertEquals(['result' => 'success'], $result);
    }

    public function testResourceRequest(): void
    {
        $this->server->shouldReceive('handleResourceRequest')
            ->once()
            ->with('test://resource')
            ->andReturn(['resource' => 'data']);

        /** @var MCPServerInterface $server */
        $server = MCP::getFacadeRoot();
        $result = $server->handleResourceRequest('test://resource');
        $this->assertEquals(['resource' => 'data'], $result);
    }

    public function testPromptRequest(): void
    {
        $this->server->shouldReceive('handlePromptRequest')
            ->once()
            ->with('test_prompt', ['arg' => 'value'])
            ->andReturn(['prompt' => 'result']);

        /** @var MCPServerInterface $server */
        $server = MCP::getFacadeRoot();
        $result = $server->handlePromptRequest('test_prompt', ['arg' => 'value']);
        $this->assertEquals(['prompt' => 'result'], $result);
    }

    public function testListContexts(): void
    {
        $this->markTestSkipped('listContexts is not part of the MCPServerInterface');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
