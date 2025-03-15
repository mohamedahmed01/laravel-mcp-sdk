<?php

require __DIR__ . '/../vendor/autoload.php';

use LaravelMCP\MCP\MCPClient;

// Configure the client
config([
    'mcp.base_url' => 'http://127.0.0.1:8080',
    'mcp.api_key' => 'test-key'
]);

// Create client instance
$client = new MCPClient();

// Example 1: Tool Call
try {
    $result = $client->createContext([
        'type' => 'tool_call',
        'name' => 'example_tool',
        'arguments' => ['arg1' => 'value1', 'arg2' => 'value2']
    ]);
    echo "Tool Call Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error making tool call: " . $e->getMessage() . "\n";
}

// Example 2: Resource Request
try {
    $result = $client->createContext([
        'type' => 'resource_request',
        'uri' => 'example://resource/123'
    ]);
    echo "Resource Request Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error making resource request: " . $e->getMessage() . "\n";
}

// Example 3: Prompt Request
try {
    $result = $client->createContext([
        'type' => 'prompt_request',
        'name' => 'example_prompt',
        'arguments' => ['context' => 'This is a test prompt']
    ]);
    echo "Prompt Request Result:\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error making prompt request: " . $e->getMessage() . "\n";
} 