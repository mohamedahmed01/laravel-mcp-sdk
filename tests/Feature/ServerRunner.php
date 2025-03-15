<?php

namespace Tests\Feature;

use LaravelMCP\MCP\Server\MCPServer;
use LaravelMCP\MCP\Transport\HttpTransport;
use Tests\TestCase;

class ServerRunner extends TestCase
{
    public function testRunServer(): void
    {
        $server = new MCPServer();
        $transport = new HttpTransport($server, [
            'host' => '127.0.0.1',
            'port' => 8080,
        ]);

        $server->setTransport($transport);
        $server->initialize();

        // Add a test tool
        $server->addTool('test_tool', function (array $arguments) {
            return ['result' => 'success'];
        }, 'A test tool');

        // Add a health check endpoint
        $server->addResource('/health', function () {
            return ['status' => 'ok'];
        });

        // Start the server
        $server->start();
    }
}
