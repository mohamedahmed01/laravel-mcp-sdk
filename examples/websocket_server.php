<?php

require __DIR__ . '/../vendor/autoload.php';

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\WebSocketTransport;

// Create a simple server implementation with real-time updates
class RealTimeServer implements MCPServerInterface
{
    private array $clients = [];

    public function handleToolCall(string $name, array $arguments = []): array
    {
        // Simulate a long-running task
        $steps = 5;
        for ($i = 1; $i <= $steps; $i++) {
            $progress = [
                'status' => 'progress',
                'tool' => $name,
                'step' => $i,
                'total_steps' => $steps,
                'message' => "Processing step {$i} of {$steps}"
            ];
            
            // Send progress to all connected clients
            foreach ($this->clients as $client) {
                $client->send(json_encode($progress));
            }
            
            // Simulate work
            sleep(1);
        }

        return [
            'status' => 'success',
            'tool' => $name,
            'args' => $arguments,
            'result' => "Completed tool {$name} execution"
        ];
    }

    public function handleResourceRequest(string $uri): array
    {
        return [
            'status' => 'success',
            'uri' => $uri,
            'content' => "Real-time resource content for {$uri}"
        ];
    }

    public function handlePromptRequest(string $name, array $arguments = []): array
    {
        // Simulate streaming response
        $words = explode(' ', 'This is a streaming response that comes word by word');
        foreach ($words as $word) {
            $progress = [
                'status' => 'streaming',
                'prompt' => $name,
                'token' => $word
            ];
            
            // Send each word to all connected clients
            foreach ($this->clients as $client) {
                $client->send(json_encode($progress));
            }
            
            // Small delay between words
            usleep(500000); // 0.5 seconds
        }

        return [
            'status' => 'success',
            'prompt' => $name,
            'args' => $arguments,
            'response' => "Completed streaming response"
        ];
    }

    public function addClient($client): void
    {
        $this->clients[] = $client;
    }

    public function removeClient($client): void
    {
        $index = array_search($client, $this->clients);
        if ($index !== false) {
            unset($this->clients[$index]);
        }
    }
}

// Create server instance
$server = new RealTimeServer();

// Create WebSocket transport
$transport = new WebSocketTransport($server, [
    'host' => '127.0.0.1',
    'port' => 8080
]);

echo "Starting MCP WebSocket server on ws://127.0.0.1:8080\n";
echo "Press Ctrl+C to stop\n";

// Start the server
$transport->start(); 