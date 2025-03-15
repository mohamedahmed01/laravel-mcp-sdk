# MCP Examples

This directory contains example implementations of the Laravel MCP package, demonstrating different transport methods and use cases.

## Prerequisites

Before running the examples, make sure you have:
1. PHP 8.1 or higher installed
2. Composer dependencies installed (`composer install`)
3. Terminal access to run PHP scripts

## Examples Overview

### 1. HTTP Transport Example

Basic request/response communication using HTTP transport.

#### Server (`http_server.php`)
- Implements a simple MCP server
- Handles tool calls, resource requests, and prompts
- Uses HTTP transport on port 8080

To run:
```bash
php http_server.php
```

#### Client (`http_client.php`)
- Demonstrates making requests to the HTTP server
- Shows error handling
- Includes examples of all request types

To run (in a separate terminal):
```bash
php http_client.php
```

### 2. WebSocket Transport Example

Real-time bidirectional communication with progress updates and streaming.

#### Server (`websocket_server.php`)
- Implements a real-time MCP server
- Demonstrates progress updates and streaming
- Manages client connections
- Simulates long-running tasks

To run:
```bash
php websocket_server.php
```

#### Client (`websocket_client.php`)
- Connects to WebSocket server
- Handles different message types
- Shows progress and streaming updates
- Includes error handling

To run (in a separate terminal):
```bash
php websocket_client.php
```

### 3. CLI Tool Example (`cli_tool.php`)

Interactive command-line interface using STDIO transport.

Features:
- Calculator tool with basic operations
- Greeting tool with customizable name
- File reading capability
- Built-in help system

To run:
```bash
php cli_tool.php
```

Available commands:
```json
# Get help
{"type": "prompt_request", "name": "help"}

# Greet someone
{"type": "tool_call", "name": "greet", "arguments": {"name": "John"}}

# Calculate
{"type": "tool_call", "name": "calculate", "arguments": {"num1": 10, "num2": 5, "operation": "+"}}

# Read a file
{"type": "resource_request", "uri": "file://path/to/file.txt"}
```

## Common Patterns

All examples demonstrate:
1. Implementing the `MCPServerInterface`
2. Setting up appropriate transport
3. Handling different request types
4. Error handling
5. Response formatting

## Troubleshooting

1. **Port already in use**
   - Change the port number in the server configuration
   - Make sure no other service is using port 8080

2. **Connection refused**
   - Ensure the server is running
   - Check if the host and port match between client and server

3. **Permission issues with file reading**
   - Ensure PHP has read permissions for the files
   - Use absolute paths or relative paths from the script location 