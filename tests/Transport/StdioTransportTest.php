<?php

namespace Tests\Transport;

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\StdioTransport;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class StdioTransportTest extends TestCase
{
    /** @var MockInterface&MCPServerInterface */
    protected $server;

    /** @var resource */
    private $stdin;

    /** @var resource */
    private $stdout;

    private StdioTransport $transport;

    /** @var resource|null */
    protected $stdinBackup;
    /** @var resource|null */
    protected $stdoutBackup;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup original STDIN and STDOUT
        $this->stdinBackup = defined('STDIN') ? STDIN : null;
        $this->stdoutBackup = defined('STDOUT') ? STDOUT : null;

        // Create temporary streams
        $stdin = fopen('php://memory', 'r+');
        if ($stdin === false) {
            $this->fail('Failed to open stdin stream');
        }
        $this->stdin = $stdin;

        $stdout = fopen('php://memory', 'r+');
        if ($stdout === false) {
            $this->fail('Failed to open stdout stream');
        }
        $this->stdout = $stdout;

        /** @var MockInterface&MCPServerInterface $server */
        $server = Mockery::mock(MCPServerInterface::class);
        $this->server = $server;

        $this->transport = new StdioTransport($this->server);

        // Set the streams after construction
        $reflection = new \ReflectionClass($this->transport);

        $stdinProp = $reflection->getProperty('stdin');
        $stdinProp->setAccessible(true);
        $stdinProp->setValue($this->transport, $this->stdin);

        $stdoutProp = $reflection->getProperty('stdout');
        $stdoutProp->setAccessible(true);
        $stdoutProp->setValue($this->transport, $this->stdout);
    }

    public function testConstructor(): void
    {
        $transport = new StdioTransport($this->server);
        $this->assertInstanceOf(StdioTransport::class, $transport);
    }

    public function testSendAndReceive(): void
    {
        $message = ['type' => 'test', 'data' => 'value'];
        $messageJson = json_encode($message);
        if ($messageJson === false) {
            $this->fail('Failed to encode message');
        }

        $written = fwrite($this->stdout, $messageJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdout');
        }

        rewind($this->stdout);
        $this->transport->send($message);

        $content = stream_get_contents($this->stdout);
        if ($content === false) {
            $this->fail('Failed to read from stdout');
        }

        $this->assertEquals($messageJson . "\n", $content);

        // Test receive
        $responseMessage = ['type' => 'response', 'data' => 'test'];
        $responseJson = json_encode($responseMessage);
        if ($responseJson === false) {
            $this->fail('Failed to encode response');
        }

        $written = fwrite($this->stdin, $responseJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin');
        }

        rewind($this->stdin);
        $received = $this->transport->receive();

        $this->assertEquals($responseMessage, $received);
    }

    public function testRunningState(): void
    {
        $this->assertFalse($this->transport->isRunning());

        $this->transport->start();
        $this->assertTrue($this->transport->isRunning());

        $this->transport->stop();
        $this->assertFalse($this->transport->isRunning());
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

        $written = fwrite($this->stdin, $requestJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin stream');
        }
        rewind($this->stdin);

        $result = $this->transport->receive();
        $this->assertEquals(['result' => 'success'], $result);
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

        $written = fwrite($this->stdin, $requestJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin stream');
        }
        rewind($this->stdin);

        $result = $this->transport->receive();
        $this->assertEquals(['resource' => 'data'], $result);
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

        $written = fwrite($this->stdin, $requestJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin stream');
        }
        rewind($this->stdin);

        $result = $this->transport->receive();
        $this->assertEquals(['prompt' => 'result'], $result);
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

        $written = fwrite($this->stdin, $requestJson . "\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin stream');
        }
        rewind($this->stdin);

        $result = $this->transport->receive();
        $this->assertEquals(['error' => 'Unknown message type: unknown_type'], $result);
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

        // Restore original STDIN and STDOUT if they were defined
        if ($this->stdinBackup !== null) {
            define('STDIN', $this->stdinBackup);
        }
        if ($this->stdoutBackup !== null) {
            define('STDOUT', $this->stdoutBackup);
        }

        // Close temporary streams
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }
        if (is_resource($this->stdout)) {
            fclose($this->stdout);
        }

        if ($this->transport->isRunning()) {
            $this->transport->stop();
        }

        Mockery::close();
    }

    public function testInvalidJsonSend(): void
    {
        $this->expectException(\JsonException::class);
        $this->transport->send(['invalid' => pack('H*', 'c3')]);
    }

    public function testInvalidJsonReceive(): void
    {
        $written = fwrite($this->stdin, "invalid json\n");
        if ($written === false) {
            $this->fail('Failed to write to stdin stream');
        }
        rewind($this->stdin);

        $this->expectException(\JsonException::class);
        $this->transport->receive();
    }
}
