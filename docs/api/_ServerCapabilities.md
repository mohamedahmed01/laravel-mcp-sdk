# ServerCapabilities

Namespace: ``

Represents the capabilities of an MCP server.

This class manages information about the various capabilities and features
supported by an MCP server instance. It includes experimental features,
logging configuration, and support for prompts, resources, and tools.

Capability Categories:
- Experimental: Beta or in-development features
- Logging: Log levels, formats, and destinations
- Prompts: Interactive message handling
- Resources: Content and file management
- Tools: Command execution and processing

Configuration Format:
```php
[
    'experimental' => [
        'feature_name' => true|false,
        // Other experimental flags
    ],
    'logging' => {
        'level' => 'debug|info|warning|error',
        'format' => 'json|text',
        'destination' => 'file|stdout'
    },
    'prompts' => [
        // Prompt configuration
    ],
    'resources' => [
        // Resource configuration
    ],
    'tools' => [
        // Tool configuration
    ]
]
```

@package LaravelMCP\MCP\Capabilities

## Methods

### __construct

Create a new server capabilities instance.

Initializes a capabilities configuration with optional components.
Each component can be null if the capability is not supported or
not configured.

Example:
```php
$capabilities = new ServerCapabilities(
    experimental: ['new_feature' => true],
    logging: (object)['level' => 'debug'],
    prompts: new PromptsCapability(),
    resources: new ResourcesCapability(),
    tools: new ToolsCapability()
);
```

@param array|null $experimental Experimental features configuration
@param object|null $logging Logging configuration
@param PromptsCapability|null $prompts Prompts capability configuration
@param ResourcesCapability|null $resources Resources capability configuration
@param ToolsCapability|null $tools Tools capability configuration

### getExperimental

Get the experimental features configuration.

Returns the configuration for experimental or beta features.
These features may not be stable and could change in future releases.

@return array|null The experimental features configuration

### getLogging

Get the logging configuration.

Returns the configuration for log handling, including:
- Log levels (debug, info, warning, error)
- Output format (JSON, text)
- Destination (file, stdout)

@return object|null The logging configuration

### getPrompts

Get the prompts capability configuration.

Returns the configuration for handling interactive prompts,
including message formats, validation rules, and handlers.

@return PromptsCapability|null The prompts capability

### getResources

Get the resources capability configuration.

Returns the configuration for managing resources, including:
- File handling
- Content types
- Access control
- URI patterns

@return ResourcesCapability|null The resources capability

### getTools

Get the tools capability configuration.

Returns the configuration for available tools, including:
- Command handlers
- Parameter validation
- Execution rules
- Security settings

@return ToolsCapability|null The tools capability

### toArray

Convert the capabilities to an array format.

Transforms the capabilities configuration into a structured array
that can be serialized or transmitted. Only includes non-null
capabilities in the output.

Example output:
```php
[
    'experimental' => ['feature' => true],
    'logging' => ['level' => 'debug'],
    'prompts' => [...],
    'resources' => [...],
    'tools' => [...]
]
```

@return array The capabilities data as a key-value array

### create

Create a new instance from an array of data.

Factory method that constructs a ServerCapabilities instance from
a configuration array. This is useful for deserializing stored
configurations or processing API responses.

Example:
```php
$capabilities = ServerCapabilities::create([
    'experimental' => ['feature' => true],
    'logging' => ['level' => 'debug'],
    'prompts' => [...],
    'resources' => [...],
    'tools' => [...]
]);
```

@param array $data The data to create the instance from
@return static A new instance of the capabilities

