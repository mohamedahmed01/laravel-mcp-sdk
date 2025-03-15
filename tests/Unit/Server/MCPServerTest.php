<?php

namespace Tests\Unit\Server;

use InvalidArgumentException;
use LaravelMCP\MCP\Contracts\PromptInterface;
use LaravelMCP\MCP\Contracts\ResourceInterface;
use LaravelMCP\MCP\Contracts\ResourceTemplateInterface;
use LaravelMCP\MCP\Contracts\ToolInterface;
use LaravelMCP\MCP\Contracts\TransportInterface;
use LaravelMCP\MCP\Root;
use LaravelMCP\MCP\Sampling\ModelPreferences;
use LaravelMCP\MCP\Server\MCPServer;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class MCPServerTest extends TestCase
{
    private MCPServer $server;
    /** @var MockInterface&TransportInterface */
    private $mockTransport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = new MCPServer();
        /** @var MockInterface&TransportInterface $mockTransport */
        $mockTransport = \Mockery::mock(TransportInterface::class);
        $mockTransport->allows('receive')->andReturn([]);
        $mockTransport->allows('isRunning')->andReturn(true);

        $this->mockTransport = $mockTransport;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function createServer(): MCPServer
    {
        return new MCPServer();
    }

    public function testInitializeWithTransport(): void
    {
        $server = new MCPServer();
        $transport = $this->createMock(TransportInterface::class);
        $server->setTransport($transport);

        // Should not throw an exception
        $server->initialize();

        $this->assertSame($transport, $server->getTransport());
    }

    public function testAddTool(): void
    {
        $server = new MCPServer();
        $name = 'test_tool';
        $description = 'Test tool description';
        $handler = function () {
            return ['result' => true];
        };

        $server->addTool($name, $handler, $description);

        $tools = $server->getTools();
        $this->assertArrayHasKey($name, $tools);
        $this->assertInstanceOf(ToolInterface::class, $tools[$name]);
        $this->assertEquals($name, $tools[$name]->getName());
        $this->assertEquals($description, $tools[$name]->getDescription());
        $this->assertEquals(['result' => true], $tools[$name]->handle([]));
    }

    public function testAddToolInterface(): void
    {
        $mockTool = $this->createMock(ToolInterface::class);
        $mockTool->method('getName')->willReturn('mock_tool');

        $this->server->addToolInterface($mockTool);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey('mock_tool', $tools);
        $this->assertSame($mockTool, $tools['mock_tool']);
    }

    public function testAddResource(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $server->addResource($uri, function () use ($content) {
            return $content;
        }, 'Test resource description');

        $resources = $server->getResources();
        $this->assertArrayHasKey($uri, $resources);
        $this->assertInstanceOf(ResourceInterface::class, $resources[$uri]);
        $this->assertEquals($uri, $resources[$uri]->getUri());
        $this->assertEquals($content, $resources[$uri]->getContent());
    }

    public function testAddResourceInterface(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $uri = 'test://resource';
        $resource->method('getUri')->willReturn($uri);

        $server = $this->createServer();
        $server->addResourceInterface($resource);

        $resources = $server->getResources();
        $this->assertArrayHasKey($uri, $resources);
        $this->assertSame($resource, $resources[$uri]);
    }

    public function testAddResourceTemplate(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $template = $this->createMock(ResourceTemplateInterface::class);
        $template->method('render')->willReturn($content);

        $server->addResourceTemplate($uri, $template);
        $this->assertTrue($server->hasResource($uri));
        $this->assertEquals($content, $server->handleResourceRequest($uri));
    }

    public function testAddResourceTemplateWithExistingUri(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $template = $this->createMock(ResourceTemplateInterface::class);
        $template->method('render')->willReturn($content);

        $server->addResourceTemplate($uri, $template);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resource already exists: test://resource');
        $server->addResourceTemplate($uri, $template);
    }

    public function testHasResource(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $server->addResource($uri, function () use ($content) {
            return $content;
        });

        $this->assertTrue($server->hasResource($uri));
        $this->assertFalse($server->hasResource('test://unknown'));
    }

    public function testGetResources(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $server->addResource($uri, function () use ($content) {
            return $content;
        });

        $resources = $server->getResources();
        $this->assertArrayHasKey($uri, $resources);
        $this->assertInstanceOf(ResourceInterface::class, $resources[$uri]);
        $this->assertEquals($uri, $resources[$uri]->getUri());
        $this->assertEquals($content, $resources[$uri]->getContent());
    }

    public function testAddPrompt(): void
    {
        $name = 'test_prompt';
        $messages = ['message1', 'message2'];
        $description = 'Test prompt description';

        $this->server->addPrompt($name, $messages, $description);

        $prompts = $this->server->getPrompts();
        $this->assertArrayHasKey($name, $prompts);
        $this->assertInstanceOf(PromptInterface::class, $prompts[$name]);
        $this->assertEquals($name, $prompts[$name]->getName());
        $this->assertEquals($description, $prompts[$name]->getDescription());
        $this->assertEquals($messages, $prompts[$name]->getArguments());
    }

    public function testAddPromptInterface(): void
    {
        $prompt = $this->createMock(PromptInterface::class);
        $prompt->expects($this->once())
            ->method('getName')
            ->willReturn('test');

        $this->server->addPromptInterface($prompt);
        $this->assertSame($prompt, $this->server->getPrompt('test'));
    }

    public function testAddRoot(): void
    {
        $server = new MCPServer();
        $uri = 'file:///test/path';
        $root = new Root($uri, 'test_root');

        $server->addRoot($root);

        $roots = $server->getRoots();
        $this->assertArrayHasKey('/test/path', $roots);
        $this->assertSame($root, $roots['/test/path']);
    }

    public function testGetCapabilities(): void
    {
        $server = new MCPServer();
        $capabilities = $server->getCapabilities();

        $this->assertIsArray($capabilities);
        $this->assertArrayHasKey('logging', $capabilities);
        $this->assertArrayHasKey('progress', $capabilities);
        $this->assertArrayHasKey('completion', $capabilities);
        $this->assertTrue($capabilities['logging']);
        $this->assertTrue($capabilities['progress']);
        $this->assertTrue($capabilities['completion']);
    }

    public function testRegisterTool(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            return ['result' => $args];
        };

        $this->server->registerTool($name, $handler);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey($name, $tools);
        $this->assertInstanceOf(ToolInterface::class, $tools[$name]);
        $this->assertEquals($name, $tools[$name]->getName());
        $this->assertEquals(['result' => ['test' => 'value']], $tools[$name]->handle(['test' => 'value']));
    }

    public function testRegisterResource(): void
    {
        $uri = 'test_resource';
        $handler = function (array $arguments = []) {
            return ['data' => 'test'];
        };

        $this->server->registerResource($uri, $handler);
        $resources = $this->server->getResources();

        $this->assertArrayHasKey($uri, $resources);
        $this->assertInstanceOf(ResourceInterface::class, $resources[$uri]);
        $this->assertEquals($uri, $resources[$uri]->getUri());
        $result = $handler([]);
        $this->assertEquals(['data' => 'test'], $result);
    }

    public function testRegisterPrompt(): void
    {
        $handler = function () {
        };
        $this->server->registerPrompt('test', $handler);

        $prompt = $this->server->getPrompt('test');
        $this->assertInstanceOf(PromptInterface::class, $prompt);
        $this->assertEquals('test', $prompt->getName());
        $this->assertSame($handler, $prompt->getHandler());
    }

    public function testHandleToolRequest(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $name = 'test_tool';
        $handler = function (array $args): array {
            return ['result' => $args];
        };

        $this->server->addTool($name, $handler, 'Test tool description');
        $result = $this->server->handleToolCall($name, ['test' => 'value']);
        $this->assertEquals(['result' => ['test' => 'value']], $result);
    }

    public function testHandleResourceRequest(): void
    {
        $server = $this->createServer();
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getUri')->willReturn($uri);
        $resource->method('getContent')->willReturn($content);

        $server->addResource($uri, function () use ($content) {
            return $content;
        });

        $result = $server->handleResourceRequest($uri);
        $this->assertEquals($content, $result);
    }

    public function testHandlePromptRequest(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $name = 'test_prompt';
        $messages = ['message1', 'message2'];

        $this->server->addPrompt($name, $messages, 'Test prompt description');
        $result = $this->server->handlePromptRequest($name, []);
        $this->assertEquals($messages, $result);
    }

    public function testHandleInvalidRequest(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown tool: invalid');
        $this->server->handleToolCall('invalid', []);
    }

    public function testHandleNonExistentTool(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown tool: non_existent');
        $this->server->handleToolCall('non_existent', []);
    }

    public function testHandleNonExistentResource(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown resource: non_existent');
        $this->server->handleResourceRequest('non_existent');
    }

    public function testHandleNonExistentPrompt(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->server->initialize();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown prompt: non_existent');
        $this->server->handlePromptRequest('non_existent', []);
    }

    public function testSetModelPreferences(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.8,
            hints: ['top_p' => 0.9]
        );

        $this->server->setModelPreferences($preferences);
        $this->assertEquals($preferences, $this->server->getModelPreferences());
    }

    public function testGetDefaultModelPreferences(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.7,
            hints: ['top_p' => 1.0]
        );
        $this->server->setModelPreferences($preferences);

        $result = $this->server->getModelPreferences();
        $this->assertInstanceOf(ModelPreferences::class, $result);

        $preferencesArray = $result->toArray();
        $this->assertEquals(0.7, $preferencesArray['temperature']);
        $this->assertEquals(['top_p' => 1.0], $preferencesArray['hints']);
    }

    public function testSendProgress(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->with([
                'type' => 'progress',
                'progress' => 0.5,
                'token' => 'test-token',
                'total' => null,
            ]);

        $this->server->setTransport($transport);
        $this->server->sendProgress(0.5, 'test-token');
    }

    public function testSendLog(): void
    {
        $this->server->setTransport($this->mockTransport);
        $this->mockTransport->shouldReceive('send')
            ->once()
            ->with(\Mockery::on(function ($arg) {
                return isset($arg['type']) && $arg['type'] === 'log' &&
                    isset($arg['data']) && $arg['data'] === 'test message' &&
                    isset($arg['level']) && $arg['level'] === 'info' &&
                    isset($arg['logger']) && $arg['logger'] === 'test_logger';
            }))
            ->andReturn(null);

        $this->server->sendLog('test message', 'info', 'test_logger');
        $this->assertTrue(true, 'Log was sent successfully');
    }

    public function testModelPreferences(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.5,
            hints: ['top_p' => 0.8, 'presence_penalty' => 0.3]
        );

        $this->server->setModelPreferences($preferences);
        $this->assertSame($preferences, $this->server->getModelPreferences());
    }

    public function testStartAndStop(): void
    {
        $this->server->setTransport($this->mockTransport);

        // Set up expectations for this specific test
        $this->mockTransport->shouldReceive('start')
            ->once()
            ->withNoArgs()
            ->andReturnNull();
        $this->mockTransport->shouldReceive('stop')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $this->server->start();
        $this->assertTrue($this->mockTransport->isRunning(), 'Server should be running after start');

        $this->server->stop();
        $this->assertTrue(true, 'Server was stopped successfully');
    }

    public function testToolWithNonArrayReturn(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            return 'string result';
        };

        $this->server->addTool($name, $handler);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey($name, $tools);
        $this->assertEquals(['string result'], $tools[$name]->handle([]));
    }

    public function testToolWithException(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            throw new \RuntimeException('Tool error');
        };

        $this->server->addTool($name, $handler);
        $tools = $this->server->getTools();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tool error');
        $tools[$name]->handle([]);
    }

    public function testResourceWithInvalidContent(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getUri')->willReturn('/test');
        $resource->method('handle')->willReturn('invalid');

        $this->server->addResourceInterface($resource);

        $result = $this->server->handleResourceRequest('/test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('invalid', $result['content']);
    }

    public function testPromptWithInvalidMessages(): void
    {
        $name = 'test_prompt';
        $messages = [['invalid_key' => 'value']]; // Missing required 'role' and 'content' keys

        $this->server->addPrompt($name, $messages, 'Test prompt');
        $prompts = $this->server->getPrompts();

        $this->assertArrayHasKey($name, $prompts);
        $this->assertInstanceOf(PromptInterface::class, $prompts[$name]);
        $this->assertEquals($name, $prompts[$name]->getName());
        $this->assertEquals($messages, $prompts[$name]->getArguments());
    }

    public function testResourceTemplateWithValidation(): void
    {
        $uri = 'test://template';
        $mockTemplate = $this->createMock(ResourceTemplateInterface::class);
        $mockTemplate->method('render')->willReturn(['test' => 'data']);
        $mockTemplate->method('expandUri')
            ->willThrowException(new \InvalidArgumentException('Invalid template parameters'));

        $this->server->addResourceTemplate($uri, $mockTemplate);
        $templates = $this->server->getResourceTemplates();

        $this->assertArrayHasKey($uri, $templates);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid template parameters');
        $templates[$uri]->expandUri([]);
    }

    public function testPromptWithMissingArguments(): void
    {
        $name = 'test_prompt';
        $messages = ['message1', 'message2'];
        $description = 'Test prompt description';

        $this->server->addPrompt($name, $messages, $description);
        $prompts = $this->server->getPrompts();

        $this->assertArrayHasKey($name, $prompts);
        $this->assertEquals($messages, $prompts[$name]->getArguments());
    }

    public function testToolWithCustomParameters(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            return $args;
        };
        $parameters = ['param1' => ['type' => 'string']];

        $this->server->registerTool($name, $handler, $parameters);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey($name, $tools);
        $this->assertEquals($parameters, $tools[$name]->getParameters());
    }

    public function testHandleToolCallWithValidation(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            if (! isset($args['required_param'])) {
                throw new \InvalidArgumentException('Missing required parameter');
            }

            return $args;
        };
        $parameters = [
            'required_param' => ['type' => 'string', 'required' => true],
        ];

        $this->server->registerTool($name, $handler, $parameters);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameter');
        $this->server->handleToolCall($name, []);
    }

    public function testHandleResourceRequestWithMimeType(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getUri')->willReturn('/test');
        $resource->method('handle')->willReturn(['content' => 'test']);
        $resource->method('getMimeType')->willReturn('text/plain');

        $this->server->addResourceInterface($resource);

        $result = $this->server->handleResourceRequest('/test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('test', $result['content']);
        $this->assertArrayHasKey('mime_type', $result);
        $this->assertEquals('text/plain', $result['mime_type']);
    }

    public function testHandlePromptRequestWithCustomArguments(): void
    {
        $name = 'test_prompt';
        $messages = ['system' => 'System message', 'user' => 'User message'];
        $arguments = ['temperature' => 0.7, 'max_tokens' => 100];

        $this->server->addPrompt($name, $messages);
        $result = $this->server->handlePromptRequest($name, $arguments);

        $this->assertEquals(array_merge($messages, $arguments), $result);
    }

    public function testResourceTemplateValidation(): void
    {
        $uri = 'test://template';
        $mockTemplate = $this->createMock(ResourceTemplateInterface::class);
        $mockTemplate->method('render')->willReturn(['test' => 'data']);

        $this->server->addResourceTemplate($uri, $mockTemplate);
        $templates = $this->server->getResourceTemplates();

        $this->assertArrayHasKey($uri, $templates);
        $this->assertSame($mockTemplate, $templates[$uri]);
    }

    public function testToolHandlerWithNonArrayReturn(): void
    {
        $name = 'test_tool';
        $handler = function ($args) {
            return 'string_result';
        };

        $this->server->addTool($name, $handler);
        $tools = $this->server->getTools();

        $this->assertArrayHasKey($name, $tools);
        $result = $tools[$name]->handle(['test' => 'value']);
        $this->assertEquals(['string_result'], $result);
    }

    public function testResourceHandlerWithNonArrayReturn(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getUri')->willReturn('/test');
        $resource->method('handle')->willReturn('test');

        $this->server->addResourceInterface($resource);

        $result = $this->server->handleResourceRequest('/test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('test', $result['content']);
    }

    public function testModelPreferencesIntegration(): void
    {
        $preferences = new ModelPreferences(
            temperature: 0.7,
            hints: ['top_p' => 0.9]
        );

        $reflection = new \ReflectionClass($this->server);
        $property = $reflection->getProperty('preferences');
        $property->setAccessible(true);
        $property->setValue($this->server, $preferences);

        $this->assertSame($preferences, $property->getValue($this->server));

        // Verify the preferences are correctly converted to array
        $preferencesArray = $preferences->toArray();
        $this->assertEquals(0.7, $preferencesArray['temperature']);
        $this->assertEquals(['top_p' => 0.9], $preferencesArray['hints']);
    }

    public function testHandleCompletion(): void
    {
        $expectedResponse = ['result' => 'success'];
        /** @var \Mockery\MockInterface&TransportInterface $mockTransport */
        $mockTransport = \Mockery::mock(TransportInterface::class);
        $mockTransport->shouldReceive('receive')
            ->once()
            ->andReturn($expectedResponse);
        $mockTransport->shouldReceive('send')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn(null);

        $this->server->setTransport($mockTransport);
        $result = $this->server->handleCompletion([], []);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testSendProgressWithoutTransport(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transport must be set before sending progress');
        $this->server->sendProgress(0.5, 'test', 1.0);
    }

    public function testSendLogWithoutTransport(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport must be set before sending logs');

        $this->server->sendLog('test message', 'info');
    }

    public function testStartWithoutTransport(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport not initialized');

        $this->server->start();
    }

    public function testStopWithoutTransport(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transport not initialized');

        $this->server->stop();
    }

    public function testHandleCompletionWithInvalidResponse(): void
    {
        $expectedResponse = ['error' => 'Invalid response'];
        /** @var \Mockery\MockInterface&TransportInterface $mockTransport */
        $mockTransport = \Mockery::mock(TransportInterface::class);
        $mockTransport->shouldReceive('receive')
            ->once()
            ->andReturn($expectedResponse);
        $mockTransport->shouldReceive('send')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturn(null);

        $this->server->setTransport($mockTransport);
        $result = $this->server->handleCompletion(['prompt' => 'test'], ['ref' => 'test']);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testSendProgressWithInvalidTotal(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $this->server->setTransport($transport);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total must be greater than zero');
        $this->server->sendProgress(0.5, 'test', 0.0);
    }

    public function testSendProgressSuccess(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->with([
                'type' => 'progress',
                'progress' => 0.5,
                'token' => 'test',
                'total' => 1.0,
            ]);

        $this->server->setTransport($transport);
        $this->server->sendProgress(0.5, 'test', 1.0);
    }

    public function testSendLogWithComplexData(): void
    {
        $this->server->setTransport($this->mockTransport);
        $complexData = [
            'nested' => [
                'array' => [1, 2, 3],
                'object' => new \stdClass(),
            ],
        ];

        $this->mockTransport->shouldReceive('send')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($complexData) {
                return isset($arg['type']) && $arg['type'] === 'log' &&
                    isset($arg['data']) && $arg['data'] === $complexData &&
                    isset($arg['level']) && $arg['level'] === 'debug' &&
                    isset($arg['logger']) && $arg['logger'] === 'test-logger';
            }))
            ->andReturn(null);

        $this->server->sendLog($complexData, 'debug', 'test-logger');
        $this->assertTrue(true, 'Log was sent successfully with complex data');
    }

    public function testHandleResourceRequestWithInvalidTemplate(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown resource: template://invalid-template');

        $this->server->handleResourceRequest('template://invalid-template');
    }

    public function testHandlePromptRequestWithInvalidArgumentType(): void
    {
        $name = 'test_prompt';
        $messages = [['role' => 'user', 'content' => 'Test message']];
        $this->server->addPrompt($name, $messages, 'Test prompt');

        $this->server->handlePromptRequest($name, ['invalid' => new \stdClass()]);
        // The prompt handler should handle the invalid argument type
        $this->assertTrue(true);
    }

    public function testHandlePromptRequestWithUnknownPrompt(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown prompt: unknown-prompt');

        $this->server->handlePromptRequest('unknown-prompt', []);
    }

    public function testSendProgressWithZeroTotal(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $this->server->setTransport($transport);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total must be greater than zero');
        $this->server->sendProgress(0.5, 'test', 0.0);
    }

    public function testSendLogWithRecursiveData(): void
    {
        $this->server->setTransport($this->mockTransport);
        $obj = new \stdClass();
        $obj->self = $obj; // Create a recursive reference

        $this->mockTransport->shouldReceive('send')
            ->once()
            ->with(\Mockery::on(function ($arg) use ($obj) {
                return isset($arg['type']) && $arg['type'] === 'log' &&
                    isset($arg['data']) && $arg['data'] === $obj &&
                    isset($arg['level']) && $arg['level'] === 'debug';
            }))
            ->andReturn(null);

        $this->server->sendLog($obj, 'debug');
        $this->assertTrue(true, 'Log was sent successfully with recursive data');
    }

    public function testTransportHandling(): void
    {
        $server = new MCPServer();

        // Initially null
        $this->assertNull($server->getTransport());

        // Set transport
        $transport = $this->createMock(TransportInterface::class);
        $server->setTransport($transport);

        // Get transport
        $this->assertSame($transport, $server->getTransport());
    }

    public function testGetCollections(): void
    {
        $server = new MCPServer();

        $this->assertIsArray($server->getTools());
        $this->assertEmpty($server->getTools());

        $this->assertIsArray($server->getResources());
        $this->assertEmpty($server->getResources());

        $this->assertIsArray($server->getResourceTemplates());
        $this->assertEmpty($server->getResourceTemplates());

        $this->assertIsArray($server->getPrompts());
        $this->assertEmpty($server->getPrompts());

        $this->assertIsArray($server->getRoots());
        $this->assertEmpty($server->getRoots());
    }

    public function testAddToolWithNonArrayReturn(): void
    {
        $name = 'test_tool';
        $handler = function () {
            return 'string_result';
        };

        $this->server->addTool($name, $handler);
        $result = $this->server->handleToolCall($name, []);
        $this->assertEquals(['string_result'], $result);
    }

    public function testAddResourceWithNonArrayContent(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $resource->method('getUri')->willReturn('/test');
        $resource->method('handle')->willReturn('test');

        $this->server->addResourceInterface($resource);

        $result = $this->server->handleResourceRequest('/test');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('test', $result['content']);
    }

    public function testAddPromptWithArguments(): void
    {
        $server = new MCPServer();
        $name = 'test_prompt';
        $messages = ['message1', 'message2'];

        $server->addPrompt($name, $messages);

        $prompts = $server->getPrompts();
        $this->assertArrayHasKey($name, $prompts);
        $this->assertEquals(
            ['message1', 'message2', 'arg1' => 'value1'],
            $prompts[$name]->handle(['arg1' => 'value1'])
        );
    }

    public function testAddToolWithEmptyDescription(): void
    {
        $name = 'test_tool';
        $handler = function () {
            return ['result' => true];
        };

        $this->server->addTool($name, $handler);

        $tools = $this->server->getTools();
        $this->assertArrayHasKey($name, $tools);
        $this->assertInstanceOf(ToolInterface::class, $tools[$name]);
        $this->assertEquals($name, $tools[$name]->getName());
        $this->assertEquals('', $tools[$name]->getDescription());
    }

    public function testAddResourceWithEmptyDescription(): void
    {
        $uri = 'test_resource';
        $content = ['data' => 'test'];

        $this->server->addResource($uri, $content);

        $resources = $this->server->getResources();
        $this->assertArrayHasKey($uri, $resources);
        $this->assertInstanceOf(ResourceInterface::class, $resources[$uri]);
        $this->assertEquals($uri, $resources[$uri]->getUri());
        $this->assertEquals('', $resources[$uri]->getDescription());
    }

    public function testAddPromptWithEmptyDescription(): void
    {
        $name = 'test_prompt';
        $messages = ['message1', 'message2'];

        $this->server->addPrompt($name, $messages);

        $prompts = $this->server->getPrompts();
        $this->assertArrayHasKey($name, $prompts);
        $this->assertInstanceOf(PromptInterface::class, $prompts[$name]);
        $this->assertEquals($name, $prompts[$name]->getName());
        $this->assertEquals('', $prompts[$name]->getDescription());
    }

    public function testToolParameters(): void
    {
        $name = 'test_tool';
        $handler = function () {
            return ['result' => true];
        };

        $tool = new class ($name, $handler) implements ToolInterface {
            private string $name;
            private $handler;
            private string $description;
            private array $parameters = [];

            public function __construct(string $name, callable $handler)
            {
                $this->name = $name;
                $this->handler = $handler;
                $this->description = '';
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function getHandler(): callable
            {
                return $this->handler;
            }

            public function getDescription(): ?string
            {
                return $this->description;
            }

            public function getParameters(): array
            {
                return $this->parameters;
            }

            public function setParameters(array $parameters): void
            {
                $this->parameters = $parameters;
            }

            public function handle(array $arguments): array
            {
                $result = ($this->handler)($arguments);

                return is_array($result) ? $result : [$result];
            }
        };

        $this->server->addTool($name, $handler);
        $tools = $this->server->getTools();
        $this->assertArrayHasKey($name, $tools);
        $this->assertInstanceOf(ToolInterface::class, $tools[$name]);
    }

    public function testHandleResourceRequestWithTemplate(): void
    {
        $uri = 'test://template';
        $mockTemplate = $this->createMock(ResourceTemplateInterface::class);
        $mockTemplate->method('render')->willReturn(['test' => 'data']);

        $this->server->addResourceTemplate($uri, $mockTemplate);
        $result = $this->server->handleResourceRequest($uri);

        $this->assertEquals(['test' => 'data'], $result);
    }

    public function testHandleResourceRequestWithUnknownUri(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown resource: test://unknown');
        $this->server->handleResourceRequest('test://unknown');
    }

    public function testGetResource(): void
    {
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $this->server->addResource($uri, $content);

        $resource = $this->server->getResource($uri);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertEquals($content, $resource->handle());
    }

    public function testAddResourceWithExistingUri(): void
    {
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $this->server->addResource($uri, $content);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resource already exists: test://resource');
        $this->server->addResource($uri, $content);
    }

    public function testHandleResourceRequestWithCallback(): void
    {
        $uri = 'test://resource';
        $content = ['test' => 'data'];
        $this->server->addResource($uri, function () use ($content) {
            return $content;
        });

        $result = $this->server->handleResourceRequest($uri);
        $this->assertEquals($content, $result);
    }
}
