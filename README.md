# Laravel MCP

A Laravel package for implementing the Model Context Protocol (MCP) in Laravel applications. This protocol facilitates communication between AI models and Laravel applications through standardized interfaces.

📚 **[View Full Documentation](https://mohamedahmed01.github.io/laravel-mcp-sdk/)**

## Requirements

### System Requirements
- PHP 8.1 or higher
- Laravel 10.x
- Composer 2.x
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- ReactPHP (for HTTP/WebSocket transport)
- ext-json (for JSON handling)

### Optional Requirements
- Redis (for WebSocket scaling)
- Supervisor (for process management)
- ext-pcntl (for signal handling)
- ext-posix (for process management)

## Features

- **Multiple Transport Options**
  - HTTP Transport (RESTful API)
  - WebSocket Transport (Real-time)
  - Stdio Transport (Command-line)
  - Configurable host and port settings

- **Server Capabilities**
  - Tool registration and execution
  - Resource management
  - Prompt handling
  - Progress tracking
  - Logging system
  - Model preferences configuration

- **Tool System**
  - Register custom tools with parameters
  - Handle tool calls with arguments
  - Return structured responses
  - Error handling and validation

- **Resource Management**
  - URI-based resource access
  - Resource templates
  - Dynamic resource handling
  - Content type support

- **Prompt System**
  - Template-based prompts
  - Dynamic argument handling
  - Context management
  - Message history support

- **Progress Tracking**
  - Real-time progress updates
  - Token-based tracking
  - Total progress support
  - Client notification system

- **Logging System**
  - Multiple log levels
  - Logger identification
  - Structured logging
  - Transport-agnostic logging

## Documentation

The project includes comprehensive PHPDoc documentation for all classes and methods. The documentation is available directly in the source code and covers:

### Core Components

- **Server Capabilities** (`src/Capabilities/ServerCapabilities.php`)
  - Server feature management
  - Experimental features configuration
  - Logging configuration
  - Component capabilities (prompts, resources, tools)

- **Client Capabilities** (`src/Capabilities/ClientCapabilities.php`)
  - Feature flag management
  - Root directory configuration
  - State serialization/deserialization

### Component Capabilities

- **Tools Capability** (`src/Capabilities/ToolsCapability.php`)
  - Tool list change tracking
  - State management
  - Change notification support

- **Resources Capability** (`src/Capabilities/ResourcesCapability.php`)
  - Resource subscription management
  - Change tracking
  - State serialization

- **Prompts Capability** (`src/Capabilities/PromptsCapability.php`)
  - Prompt list change tracking
  - State management
  - Change notifications

- **Roots Capability** (`src/Capabilities/RootsCapability.php`)
  - Root directory management
  - Directory configuration
  - Access control settings

### Server Management

- **MCP Server Command** (`src/Commands/MCPServerCommand.php`)
  - Server startup configuration
  - Transport selection
  - Signal handling
  - Graceful shutdown

Each component includes:
- Detailed class descriptions
- Feature lists and capabilities
- Configuration examples
- Usage examples
- Method documentation
- Parameter descriptions
- Return value documentation

To explore the documentation:
1. Browse the source files in the `src/` directory
2. Use your IDE's PHPDoc integration
3. Generate HTML documentation using phpDocumentor (optional)

To generate HTML documentation:
```bash
composer require --dev phpdocumentor/phpdocumentor
vendor/bin/phpdoc -d src/ -t docs/
```

## Installation

Install via Composer:

```bash
composer require laravelmcp/mcp
```

The package will automatically register its service provider and facade.

## Configuration

1. Publish the configuration:
```bash
php artisan vendor:publish --provider="LaravelMCP\MCP\MCPServiceProvider" --tag="config"
```

2. Configure environment variables:
```env
MCP_SERVER_HOST=127.0.0.1
MCP_SERVER_PORT=8080
MCP_SERVER_TRANSPORT=http
```

## Basic Usage

### Starting the Server

```bash
# Start the server with default HTTP transport
php artisan mcp:serve

# Start with specific transport and options
php artisan mcp:serve --transport=websocket --host=0.0.0.0 --port=8081

# Start with stdio transport (useful for CLI applications)
php artisan mcp:serve --transport=stdio
```

### Server Setup

```php
use LaravelMCP\MCP\Server\MCPServer;
use LaravelMCP\MCP\Transport\HttpTransport;

// Create and configure server
$server = new MCPServer();

// Configure transport
$transport = new HttpTransport($server, [
    'host' => '127.0.0.1',
    'port' => 8080
]);

$server->setTransport($transport);
$server->initialize();

// Start the server
$server->start();
```

### Using Different Transports

```php
use LaravelMCP\MCP\Transport\WebSocketTransport;
use LaravelMCP\MCP\Transport\StdioTransport;
use LaravelMCP\MCP\Transport\TransportFactory;

// Using WebSocket transport
$wsTransport = new WebSocketTransport($server, [
    'host' => '0.0.0.0',
    'port' => 8081
]);

// Using Stdio transport (for CLI applications)
$stdioTransport = new StdioTransport($server);

// Using transport factory
$factory = new TransportFactory();
$transport = $factory->create('http', $server, [
    'host' => '127.0.0.1',
    'port' => 8080
]);
```

### Registering Tools

```php
use LaravelMCP\MCP\Server\FastMCP;
use LaravelMCP\MCP\Facades\MCP;

// Using FastMCP (recommended)
$mcp = new FastMCP();

// Register a simple calculation tool
$mcp->tool('calculate', [
    'num1' => ['type' => 'number', 'required' => true],
    'num2' => ['type' => 'number', 'required' => true],
    'operation' => ['type' => 'string', 'required' => true]
])(function ($args) {
    $num1 = $args['num1'];
    $num2 = $args['num2'];
    $operation = $args['operation'];
    
    return match ($operation) {
        '+' => ['result' => $num1 + $num2],
        '-' => ['result' => $num1 - $num2],
        '*' => ['result' => $num1 * $num2],
        '/' => $num2 != 0 ? ['result' => $num1 / $num2] : ['error' => 'Division by zero'],
        default => ['error' => 'Invalid operation']
    };
});

// Register a tool with error handling
$mcp->tool('process-data', [
    'items' => ['type' => 'array', 'required' => true],
    'token' => ['type' => 'string', 'required' => false]
])(function ($args) use ($mcp) {
    try {
        $items = $args['items'];
        $token = $args['token'] ?? uniqid();
        
        foreach ($items as $index => $item) {
            // Process item
            $progress = ($index + 1) / count($items);
            MCP::sendProgress($progress, $token, count($items));
            MCP::sendLog("Processing item {$index + 1}", 'info', 'processor');
        }
        
        return ['processed' => count($items)];
    } catch (\Exception $e) {
        MCP::sendLog($e->getMessage(), 'error', 'processor');
        return ['error' => $e->getMessage()];
    }
});
```

### Managing Resources

```php
// Register a file resource
$mcp->resource('file://{path}')(function ($matches) {
    $path = $matches['path'] ?? null;
    if (!$path || !file_exists($path)) {
        return ['error' => 'File not found'];
    }
    
    return [
        'content' => file_get_contents($path),
        'metadata' => [
            'size' => filesize($path),
            'modified' => filemtime($path),
            'mime' => mime_content_type($path)
        ]
    ];
});

// Register a database resource
$mcp->resource('db://{table}/{id}')(function ($matches) {
    $table = $matches['table'] ?? null;
    $id = $matches['id'] ?? null;
    
    if (!$table || !$id) {
        return ['error' => 'Invalid parameters'];
    }
    
    try {
        $record = DB::table($table)->find($id);
        return $record ? ['data' => $record] : ['error' => 'Record not found'];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});

// Register a health check resource
$mcp->resource('/health')(function () {
    return ['status' => 'ok'];
});
```

### Handling Prompts

```php
// Basic prompt template
$mcp->prompt('code-review', [
    'language' => ['type' => 'string', 'required' => true],
    'code' => ['type' => 'string', 'required' => true]
])(function ($args) {
    return [
        ['role' => 'system', 'content' => 'You are a code review assistant.'],
        ['role' => 'user', 'content' => "Please review this {$args['language']} code:\n\n{$args['code']}"]
    ];
});

// Prompt with context
$mcp->prompt('chat', [
    'message' => ['type' => 'string', 'required' => true],
    'context' => ['type' => 'array', 'required' => false]
])(function ($args) {
    $messages = $args['context'] ?? [];
    $messages[] = [
        'role' => 'user',
        'content' => $args['message']
    ];
    return $messages;
});
```

### Progress and Logging

```php
use LaravelMCP\MCP\Facades\MCP;

// Send progress updates
MCP::sendProgress(0.5, 'task_123', 1.0);

// Send logs with different levels
MCP::sendLog('Processing started', 'info', 'processor');
MCP::sendLog('Warning: Rate limit approaching', 'warning', 'rate-limiter');
MCP::sendLog('Error occurred: Invalid input', 'error', 'validator');
MCP::sendLog('Debug: Request payload', 'debug', 'http');
```

### Model Preferences

```php
use LaravelMCP\MCP\Sampling\ModelPreferences;

// Basic model preferences
$preferences = new ModelPreferences(
    costPriority: 0.5,
    intelligencePriority: 0.8,
    speedPriority: 0.3
);
MCP::setModelPreferences($preferences);

// Model preferences with hints
$preferences = new ModelPreferences(
    costPriority: 0.7,
    intelligencePriority: 0.9,
    speedPriority: 0.4,
    hints: ['creative', 'concise']
);
MCP::setModelPreferences($preferences);

// Create from array
$preferences = ModelPreferences::create([
    'costPriority' => 0.6,
    'intelligencePriority' => 0.8,
    'speedPriority' => 0.5,
    'hints' => ['detailed', 'technical']
]);
MCP::setModelPreferences($preferences);
```

### Error Handling

```php
use LaravelMCP\MCP\Facades\MCP;

try {
    // Initialize server
    $server = new MCPServer();
    
    if (!$server->getTransport()) {
        throw new RuntimeException('Transport not initialized');
    }
    
    // Handle tool call
    $result = $server->handleToolCall('test_tool', [
        'param1' => 'value1',
        'param2' => 'value2'
    ]);
    
    // Handle resource request
    $resource = $server->handleResourceRequest('test://resource');
    
    // Handle prompt request
    $prompt = $server->handlePromptRequest('test_prompt', [
        'arg1' => 'value1'
    ]);
    
} catch (RuntimeException $e) {
    MCP::sendLog("Runtime error: {$e->getMessage()}", 'error', 'server');
} catch (\Exception $e) {
    MCP::sendLog("Unexpected error: {$e->getMessage()}", 'error', 'server');
}
```

### Fun with LLMs: Building a Code Review Assistant

Here's a complete example of building a code review assistant that can analyze code, suggest improvements, and even fix bugs:

```php
use LaravelMCP\MCP\Server\FastMCP;
use LaravelMCP\MCP\Facades\MCP;
use Illuminate\Support\Facades\Storage;

$mcp = new FastMCP();

// Register a tool for analyzing code complexity
$mcp->tool('analyze-complexity', [
    'code' => ['type' => 'string', 'required' => true],
    'language' => ['type' => 'string', 'required' => true]
])(function ($args) {
    // Simulate complexity analysis
    $metrics = [
        'cyclomatic' => random_int(1, 10),
        'cognitive' => random_int(1, 15),
        'lines' => count(explode("\n", $args['code']))
    ];
    
    return [
        'metrics' => $metrics,
        'suggestion' => $metrics['cyclomatic'] > 5 ? 'Consider breaking down this function' : 'Complexity is acceptable'
    ];
});

// Register a code improvement prompt
$mcp->prompt('suggest-improvements', [
    'code' => ['type' => 'string', 'required' => true],
    'language' => ['type' => 'string', 'required' => true],
    'context' => ['type' => 'string', 'required' => false]
])(function ($args) {
    $messages = [
        [
            'role' => 'system',
            'content' => "You are an expert {$args['language']} developer. Analyze the code and suggest improvements for:
                         1. Performance
                         2. Readability
                         3. Best practices
                         4. Potential bugs
                         Be specific and provide examples."
        ],
        [
            'role' => 'user',
            'content' => "Here's the code to review:\n\n```{$args['language']}\n{$args['code']}\n```"
        ]
    ];
    
    if (isset($args['context'])) {
        $messages[] = [
            'role' => 'user',
            'content' => "Additional context: {$args['context']}"
        ];
    }
    
    return $messages;
});

// Register a resource for storing review history
$mcp->resource('reviews://{file_hash}')(function ($matches) {
    $hash = $matches['file_hash'] ?? null;
    if (!$hash) return ['error' => 'Invalid file hash'];
    
    $reviewPath = "reviews/{$hash}.json";
    if (!Storage::exists($reviewPath)) {
        return ['error' => 'No review history found'];
    }
    
    return ['history' => json_decode(Storage::get($reviewPath), true)];
});

// Create a fun code review workflow
$mcp->tool('review-code', [
    'code' => ['type' => 'string', 'required' => true],
    'language' => ['type' => 'string', 'required' => true],
    'style' => ['type' => 'string', 'enum' => ['serious', 'fun', 'sarcastic'], 'default' => 'serious']
])(function ($args) use ($mcp) {
    $fileHash = md5($args['code']);
    $reviewToken = uniqid('review_');
    
    try {
        // Step 1: Analyze complexity
        MCP::sendProgress(0.2, $reviewToken, "Analyzing code complexity...");
        $complexity = $mcp->call('analyze-complexity', [
            'code' => $args['code'],
            'language' => $args['language']
        ]);
        
        // Step 2: Get improvement suggestions
        MCP::sendProgress(0.5, $reviewToken, "Getting expert suggestions...");
        $personality = match($args['style']) {
            'fun' => "Be playful and use coding puns, but maintain professionalism.",
            'sarcastic' => "Use witty, sarcastic humor (but stay constructive and kind).",
            default => "Be direct and professional."
        };
        
        $suggestions = $mcp->prompt('suggest-improvements', [
            'code' => $args['code'],
            'language' => $args['language'],
            'context' => "Please {$personality}\nComplexity metrics: " . json_encode($complexity['metrics'])
        ]);
        
        // Step 3: Store review history
        MCP::sendProgress(0.8, $reviewToken, "Saving review history...");
        Storage::put("reviews/{$fileHash}.json", json_encode([
            'timestamp' => now(),
            'complexity' => $complexity,
            'suggestions' => $suggestions,
            'style' => $args['style']
        ]));
        
        // Step 4: Format response
        MCP::sendProgress(1.0, $reviewToken, "Done!");
        
        $funnyComments = [
            'fun' => [
                "🎮 Game Over! Your code review is ready!",
                "🎯 Hit the target! Here's your review!",
                "🎪 Step right up to see your code review!"
            ],
            'sarcastic' => [
                "🎭 Oh look, another masterpiece to review...",
                "🎪 Ladies and gentlemen, behold this code!",
                "🎯 Well, well, well... what do we have here?"
            ],
            'serious' => [
                "✅ Code review completed",
                "📋 Analysis complete",
                "🔍 Review finished"
            ]
        ];
        
        return [
            'message' => $funnyComments[$args['style']][array_rand($funnyComments[$args['style']])],
            'complexity' => $complexity,
            'suggestions' => $suggestions,
            'history_uri' => "reviews://{$fileHash}"
        ];
        
    } catch (\Exception $e) {
        MCP::sendLog("Review error: {$e->getMessage()}", 'error', 'reviewer');
        return ['error' => "Oops! The code reviewer needs coffee! ({$e->getMessage()})"];
    }
});

// Example usage:
$result = $mcp->call('review-code', [
    'code' => '
        function fibonacci($n) {
            if ($n <= 1) return $n;
            return fibonacci($n - 1) + fibonacci($n - 2);
        }
    ',
    'language' => 'php',
    'style' => 'fun'
]);

// Output might include fun messages like:
// "🎮 Game Over! Your code review is ready!"
// With suggestions about using memoization for better performance
// and adding parameter validation! 
```

This example demonstrates:
1. Tool registration with complexity analysis
2. Custom prompt templates with personality
3. Resource management for review history
4. Progress tracking with fun messages
5. Error handling with humor
6. Integration of multiple MCP features
7. Real-world code improvement workflow

## Testing

Run the test suite:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email mohamedabdelmenem01@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information. 