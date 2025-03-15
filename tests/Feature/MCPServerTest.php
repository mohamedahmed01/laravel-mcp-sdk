<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MCPServerTest extends TestCase
{
    private ?Process $serverProcess = null;
    private Client $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = new Client([
            'http_errors' => false,
            'timeout' => 2,
        ]);
    }

    public function testServerStartsAndResponds(): void
    {
        // Start the server in a separate process
        $this->serverProcess = new Process(['php', 'vendor/bin/phpunit', '--process-isolation', 'tests/Feature/ServerRunner.php']);
        $this->serverProcess->start();

        // Wait for the server to start, with timeout
        $startTime = time();
        $timeout = 5;
        $serverStarted = false;

        while (time() - $startTime < $timeout) {
            try {
                $response = $this->httpClient->get('http://127.0.0.1:8080/health');
                if ($response->getStatusCode() === 200) {
                    $serverStarted = true;

                    break;
                }
            } catch (ConnectException $e) {
                // Server not ready yet
                usleep(100000); // Wait 100ms before trying again

                continue;
            }
        }

        $this->assertTrue($serverStarted, 'Server failed to start within timeout period');

        try {
            // Test health endpoint
            $response = $this->httpClient->get('http://127.0.0.1:8080/health');
            $this->assertEquals(200, $response->getStatusCode());
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals(['status' => 'ok'], $body);

            // Test tool call
            $response = $this->httpClient->post('http://127.0.0.1:8080', [
                'json' => [
                    'type' => 'tool_call',
                    'name' => 'test_tool',
                    'arguments' => ['test' => 'data'],
                ],
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals(['result' => 'success'], $body);

            // Test invalid request (empty body)
            $response = $this->httpClient->post('http://127.0.0.1:8080');
            $this->assertEquals(400, $response->getStatusCode());
            $this->assertEquals('Invalid JSON', $response->getBody()->getContents());

            // Test invalid request (invalid JSON)
            $response = $this->httpClient->post('http://127.0.0.1:8080', [
                'body' => 'not json',
            ]);
            $this->assertEquals(400, $response->getStatusCode());
            $this->assertEquals('Invalid JSON', $response->getBody()->getContents());

            // Test invalid request (missing type)
            $response = $this->httpClient->post('http://127.0.0.1:8080', [
                'json' => ['foo' => 'bar'],
            ]);
            $this->assertEquals(500, $response->getStatusCode());
            $this->assertEquals('Unknown message type: ', $response->getBody()->getContents());

            // Test invalid tool name
            $response = $this->httpClient->post('http://127.0.0.1:8080', [
                'json' => [
                    'type' => 'tool_call',
                    'name' => 'nonexistent_tool',
                    'arguments' => ['test' => 'data'],
                ],
            ]);
            $this->assertEquals(500, $response->getStatusCode());
            $this->assertEquals('Unknown tool: nonexistent_tool', $response->getBody()->getContents());
        } finally {
            // Stop the server
            if ($this->serverProcess && $this->serverProcess->isRunning()) {
                $this->serverProcess->stop();
            }
        }
    }

    protected function tearDown(): void
    {
        // Ensure the server is stopped
        if ($this->serverProcess && $this->serverProcess->isRunning()) {
            $this->serverProcess->stop();

            // Wait for the process to stop
            $this->serverProcess->wait();
        }

        parent::tearDown();
    }
}
