<?php

require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Client\WebSocket;
use React\EventLoop\Loop;

// Connect to WebSocket server
\Ratchet\Client\connect('ws://127.0.0.1:8080', [], [], $loop = Loop::get())->then(function(WebSocket $conn) {
    // Example 1: Tool Call with progress updates
    $conn->send(json_encode([
        'type' => 'tool_call',
        'name' => 'long_running_tool',
        'arguments' => ['param1' => 'value1']
    ]));

    // Example 2: Resource Request
    $conn->send(json_encode([
        'type' => 'resource_request',
        'uri' => 'example://resource/123'
    ]));

    // Example 3: Prompt Request with streaming response
    $conn->send(json_encode([
        'type' => 'prompt_request',
        'name' => 'streaming_prompt',
        'arguments' => ['context' => 'Generate a streaming response']
    ]));

    // Handle incoming messages
    $conn->on('message', function($msg) {
        $data = json_decode($msg, true);
        
        switch ($data['status'] ?? '') {
            case 'progress':
                echo "Progress: Step {$data['step']}/{$data['total_steps']} - {$data['message']}\n";
                break;
            
            case 'streaming':
                echo "Streaming token: {$data['token']}\n";
                break;
            
            case 'success':
                echo "Success Response:\n";
                echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
                break;
            
            default:
                echo "Received message:\n";
                echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        }
    });

}, function ($e) {
    echo "Could not connect: {$e->getMessage()}\n";
});

// Run the event loop
$loop->run();
