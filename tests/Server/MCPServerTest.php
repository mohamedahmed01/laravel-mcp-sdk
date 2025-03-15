<?php

namespace LaravelMCP\MCP\Tests\Server;

use LaravelMCP\MCP\Contracts\PromptInterface;
use LaravelMCP\MCP\Contracts\ResourceInterface;
use LaravelMCP\MCP\Contracts\ResourceTemplateInterface;
use LaravelMCP\MCP\Contracts\ToolInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use LaravelMCP\MCP\Server\MCPServer;
use PHPUnit\Framework\TestCase;

class MCPServerTest extends TestCase
{
    protected MCPServer $server;
    protected TransportInterface $transport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transport = $this->createMock(TransportInterface::class);
        $this->server = new MCPServer();
        $this->server->setTransport($this->transport);
    }

    public function testAddTool(): void
    {
        $tool = $this->createMock(ToolInterface::class);
        $tool->method('getName')->willReturn('test_tool');
        $tool->method('getDescription')->willReturn('Test tool description');
        $tool->method('handle')->with(['test' => 'value'])->willReturn(['result' => 'success']);

        $this->server->addToolInterface($tool);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey('test_tool', $tools);
        $this->assertSame($tool, $tools['test_tool']);
    }

    public function testAddResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getName')->willReturn('test_resource');
        $resource->method('getDescription')->willReturn('Test resource description');
        $resource->method('handle')->with([])->willReturn(['test' => 'data']);

        $this->server->addResourceInterface($resource);
        $resources = $this->server->getResources();

        $this->assertArrayHasKey('test_resource', $resources);
        $this->assertSame($resource, $resources['test_resource']);
    }

    public function testAddResourceTemplate(): void
    {
        $template = $this->createMock(ResourceTemplateInterface::class);
        $template->method('getUri')->willReturn('/test/{id}');
        $template->method('render')->willReturn('/test/123');

        $this->server->addResourceTemplate('/test/{id}', $template);
        $this->assertCount(1, $this->server->getResourceTemplates());
    }

    public function testAddPrompt(): void
    {
        $prompt = $this->createMock(PromptInterface::class);
        $prompt->method('getName')->willReturn('test_prompt');
        $prompt->method('getDescription')->willReturn('Test prompt description');
        $prompt->method('handle')->with(['arg' => 'value'])->willReturn(['message' => 'test']);

        $this->server->addPromptInterface($prompt);
        $prompts = $this->server->getPrompts();

        $this->assertArrayHasKey('test_prompt', $prompts);
        $this->assertSame($prompt, $prompts['test_prompt']);
    }

    public function testHandleToolCall(): void
    {
        $tool = $this->createMock(ToolInterface::class);
        $tool->method('getName')->willReturn('test_tool');
        $tool->method('handle')->with(['test' => 'value'])->willReturn(['result' => 'success']);

        $this->server->addToolInterface($tool);
        $result = $this->server->handleToolCall('test_tool', ['test' => 'value']);

        $this->assertEquals(['result' => 'success'], $result);
    }

    public function testHandleResourceRequest(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getName')->willReturn('test_resource');
        $resource->method('handle')->with([])->willReturn(['test' => 'data']);

        $this->server->addResourceInterface($resource);
        $result = $this->server->handleResourceRequest('test_resource');

        $this->assertEquals(['test' => 'data'], $result);
    }

    public function testHandlePromptRequest(): void
    {
        $prompt = $this->createMock(PromptInterface::class);
        $prompt->method('getName')->willReturn('test_prompt');
        $prompt->method('handle')->with(['arg' => 'value'])->willReturn(['message' => 'test']);

        $this->server->addPromptInterface($prompt);
        $result = $this->server->handlePromptRequest('test_prompt', ['arg' => 'value']);

        $this->assertEquals(['message' => 'test'], $result);
    }
}
