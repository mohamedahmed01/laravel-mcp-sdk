# RootsCapability

Namespace: ``

Represents the roots capability in the MCP system.

This class manages information about root directories that are available
in the MCP system. It tracks whether the roots feature is enabled and
maintains a list of configured root directories.

Root Directory Features:
- Enable/disable root directory management
- Configure multiple root directories
- Track root directory paths and metadata
- Serialize root configurations

Root Directory Configuration:
```php
[
    'path' => '/path/to/root',
    'name' => 'project-root',
    'permissions' => [
        'read' => true,
        'write' => false
    ],
    'metadata' => [
        'description' => 'Project root directory',
        'type' => 'workspace'
    ]
]
```

Example Usage:
```php
// Create a roots capability with configuration
$roots = new RootsCapability(
    enabled: true,
    roots: [
        [
            'path' => '/workspace',
            'name' => 'main'
        ]
    ]
);

// Check if roots are enabled
if ($roots->isEnabled()) {
    $configuredRoots = $roots->getRoots();
}
```

@package LaravelMCP\MCP\Capabilities

## Methods

### __construct

Create a new roots capability instance.

Initializes the roots capability with optional configuration
for enabling/disabling root directory management and setting
up root directory configurations.

Example:
```php
// Enable roots with configuration
$roots = new RootsCapability(
    enabled: true,
    roots: [
        [
            'path' => '/workspace',
            'name' => 'main',
            'permissions' => ['read' => true]
        ]
    ]
);

// Disable roots capability
$roots = new RootsCapability(enabled: false);
```

@param bool $enabled Whether the roots capability is enabled
@param array $roots List of configured root directories, each containing:
                   - path: string (required) - Absolute path to root directory
                   - name: string (required) - Unique identifier for the root
                   - permissions: array (optional) - Access control settings
                   - metadata: array (optional) - Additional root information

### isEnabled

Check if roots capability is enabled.

Determines whether root directory management is active in the
current MCP system configuration. When disabled, root directory
operations will not be available.

Example:
```php
if ($roots->isEnabled()) {
    // Root directory operations are available
    $configuredRoots = $roots->getRoots();
} else {
    // Root directory operations are disabled
}
```

@return bool True if roots capability is enabled, false otherwise

### getRoots

Get the configured root directories.

Retrieves the list of all configured root directories with their
complete configurations, including paths, names, permissions,
and metadata.

Example:
```php
$configuredRoots = $roots->getRoots();
foreach ($configuredRoots as $root) {
    echo "Root: {$root['name']} at {$root['path']}\n";
    if (isset($root['permissions'])) {
        // Handle root permissions
    }
}
```

@return array List of root directory configurations, each containing:
              - path: string - Absolute path to root directory
              - name: string - Unique identifier for the root
              - permissions: array (optional) - Access control settings
              - metadata: array (optional) - Additional root information

### toArray

Convert the capability to an array format.

Transforms the roots capability configuration into a structured
array suitable for storage or transmission. Includes both the
enabled state and the complete root directory configurations.

Example output:
```php
[
    'enabled' => true,
    'roots' => [
        [
            'path' => '/workspace',
            'name' => 'main',
            'permissions' => ['read' => true]
        ]
    ]
]
```

@return array The capability data as a key-value array

### create

Create a new instance from an array of data.

Factory method that constructs a RootsCapability instance from
a configuration array. This is useful for deserializing stored
configurations or processing API responses.

Example:
```php
$roots = RootsCapability::create([
    'enabled' => true,
    'roots' => [
        [
            'path' => '/workspace',
            'name' => 'main'
        ]
    ]
]);
```

@param array $data The data to create the instance from, containing:
                   - enabled: bool - Whether roots are enabled
                   - roots: array - List of root configurations
@return static A new instance of the capability

