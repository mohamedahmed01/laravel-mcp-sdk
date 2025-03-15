<?php

require __DIR__ . '/../vendor/autoload.php';

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\HttpTransport;

// Create a simple server implementation
class SimpleServer implements MCPServerInterface
{
    public function handleToolCall(string $name, array $arguments = []): array
    {
        return [
            'status' => 'success',
            'tool' => $name,
            'args' => $arguments,
            'result' => "Executed tool {$name} with " . count($arguments) . " arguments"
        ];
    }

    public function handleResourceRequest(string $uri): array
    {
        return [
            'status' => 'success',
            'uri' => $uri,
            'content' => "Resource content for {$uri}"
        ];
    }

    public function handlePromptRequest(string $name, array $arguments = []): array
    {
        return [
            'status' => 'success',
            'prompt' => $name,
            'args' => $arguments,
            'response' => "Generated response for prompt {$name}"
        ];
    }
}

// Create server instance
$server = new SimpleServer();

// Create HTTP transport
$transport = new HttpTransport($server, [
    'host' => '127.0.0.1',
    'port' => 8080
]);

echo "Starting MCP HTTP server on http://127.0.0.1:8080\n";
echo "Press Ctrl+C to stop\n";

// Start the server
$transport->start(); 