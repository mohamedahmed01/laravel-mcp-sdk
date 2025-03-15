<?php

namespace Tests\Unit\Server;

use Closure;
use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Server\FastMCP;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class FastMCPTest extends TestCase
{
    private FastMCP $fastMcp;
    /** @var MCPServerInterface&MockObject */
    private MCPServerInterface $mockServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockServer = $this->createMock(MCPServerInterface::class);
        $this->fastMcp = new FastMCP($this->mockServer);
    }

    public function testToolRegistration(): void
    {
        $name = 'test_tool';
        $parameters = ['param1' => 'value1'];
        $handler = function () {
        };

        $this->mockServer->expects($this->once())
            ->method('registerTool')
            ->with($name, $handler, $parameters);

        $toolClosure = $this->fastMcp->tool($name, $parameters);
        $this->assertInstanceOf(Closure::class, $toolClosure);
        $toolClosure($handler);
    }

    public function testResourceRegistration(): void
    {
        $uri = 'test_resource';
        $handler = function () {
        };

        $this->mockServer->expects($this->once())
            ->method('registerResource')
            ->with($uri, $handler);

        $resourceClosure = $this->fastMcp->resource($uri);
        $this->assertInstanceOf(Closure::class, $resourceClosure);
        $resourceClosure($handler);
    }

    public function testPromptRegistration(): void
    {
        $name = 'test_prompt';
        $arguments = ['arg1' => 'value1'];
        $handler = function () {
        };

        $this->mockServer->expects($this->once())
            ->method('registerPrompt')
            ->with($name, $handler, $arguments);

        $promptClosure = $this->fastMcp->prompt($name, $arguments);
        $this->assertInstanceOf(Closure::class, $promptClosure);
        $promptClosure($handler);
    }

    public function testLifespanRegistration(): void
    {
        $handler = function () {
        };
        $this->fastMcp->lifespan($handler);

        $this->assertSame($handler, $this->fastMcp->getLifespan());
    }

    public function testGetServer(): void
    {
        $this->assertSame($this->mockServer, $this->fastMcp->getServer());
    }

    public function testGetDependencies(): void
    {
        $this->assertIsArray($this->fastMcp->getDependencies());
        $this->assertEmpty($this->fastMcp->getDependencies());
    }

    public function testHandleCompletion(): void
    {
        $arguments = ['arg1' => 'value1'];
        $messages = ['message1'];

        $result = $this->fastMcp->handleCompletion($arguments, $messages);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testConstructorWithoutServer(): void
    {
        $fastMcp = new FastMCP();
        $this->assertSame($fastMcp, $fastMcp->getServer());
    }

    public function testToolRegistrationWithoutParameters(): void
    {
        $name = 'test_tool';
        $handler = function () {
        };

        $this->mockServer->expects($this->once())
            ->method('registerTool')
            ->with($name, $handler, []);

        $toolClosure = $this->fastMcp->tool($name);
        $toolClosure($handler);
    }

    public function testPromptRegistrationWithoutArguments(): void
    {
        $name = 'test_prompt';
        $handler = function () {
        };

        $this->mockServer->expects($this->once())
            ->method('registerPrompt')
            ->with($name, $handler, []);

        $promptClosure = $this->fastMcp->prompt($name);
        $promptClosure($handler);
    }

    public function testLifespanNotSet(): void
    {
        $fastMcp = new FastMCP();
        $this->assertNull($fastMcp->getLifespan());
    }
}
