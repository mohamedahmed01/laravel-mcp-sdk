<?php

require __DIR__ . '/../vendor/autoload.php';

use LaravelMCP\MCP\Contracts\MCPServerInterface;
use LaravelMCP\MCP\Transport\StdioTransport;

// Create a CLI tool server implementation
class CLIToolServer implements MCPServerInterface
{
    public function handleToolCall(string $name, array $arguments = []): array
    {
        switch ($name) {
            case 'greet':
                $name = $arguments['name'] ?? 'World';
                return [
                    'status' => 'success',
                    'message' => "Hello, {$name}!"
                ];
            
            case 'calculate':
                $num1 = $arguments['num1'] ?? 0;
                $num2 = $arguments['num2'] ?? 0;
                $operation = $arguments['operation'] ?? '+';
                
                $result = match ($operation) {
                    '+' => $num1 + $num2,
                    '-' => $num1 - $num2,
                    '*' => $num1 * $num2,
                    '/' => $num2 != 0 ? $num1 / $num2 : 'Cannot divide by zero',
                    default => 'Unknown operation'
                };
                
                return [
                    'status' => 'success',
                    'result' => $result
                ];
            
            default:
                return [
                    'status' => 'error',
                    'message' => "Unknown tool: {$name}"
                ];
        }
    }

    public function handleResourceRequest(string $uri): array
    {
        // Simple file reader
        if (str_starts_with($uri, 'file://')) {
            $path = substr($uri, 7);
            if (file_exists($path)) {
                return [
                    'status' => 'success',
                    'content' => file_get_contents($path)
                ];
            }
        }
        
        return [
            'status' => 'error',
            'message' => "Cannot read resource: {$uri}"
        ];
    }

    public function handlePromptRequest(string $name, array $arguments = []): array
    {
        switch ($name) {
            case 'help':
                return [
                    'status' => 'success',
                    'content' => <<<EOT
Available commands:
1. Tool Calls:
   - greet: Greets a person
     Usage: {"type": "tool_call", "name": "greet", "arguments": {"name": "John"}}
   
   - calculate: Performs basic arithmetic
     Usage: {"type": "tool_call", "name": "calculate", "arguments": {"num1": 10, "num2": 5, "operation": "+"}}

2. Resource Requests:
   - file reader:
     Usage: {"type": "resource_request", "uri": "file://path/to/file.txt"}

3. Prompts:
   - help: Shows this help message
     Usage: {"type": "prompt_request", "name": "help"}
EOT
                ];
            
            default:
                return [
                    'status' => 'error',
                    'message' => "Unknown prompt: {$name}"
                ];
        }
    }
}

// Create server instance
$server = new CLIToolServer();

// Create STDIO transport
$transport = new StdioTransport($server);

// Print usage instructions
echo "MCP CLI Tool\n";
echo "Enter JSON commands, one per line. Type 'help' for usage instructions.\n";
echo "Example: {\"type\": \"prompt_request\", \"name\": \"help\"}\n\n";

// Start the server
$transport->start(); 