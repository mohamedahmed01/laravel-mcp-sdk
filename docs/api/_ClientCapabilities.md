# ClientCapabilities

Namespace: ``

Represents the capabilities of an MCP client.

This class manages information about the features and capabilities
supported by an MCP client. It includes experimental feature flags
and root directory configurations.

Client Capability Features:
- Experimental feature management
- Root directory configuration
- State serialization/deserialization
- Feature flag control

Configuration Structure:
```php
[
    'experimental' => bool|null,    // Feature flag state
    'roots' => [                    // Root directory settings
        'enabled' => bool,
        'roots' => [
            [
                'path' => string,
                'name' => string,
                // Additional root config...
            ]
        ]
    ]
]
```

Example Usage:
```php
// Create client capabilities with experimental features
$capabilities = new ClientCapabilities(
    experimental: true,
    roots: new RootsCapability(enabled: true)
);

// Check experimental status
if ($capabilities->isExperimental()) {
    // Handle experimental features
}

// Access root configuration
if ($roots = $capabilities->getRoots()) {
    if ($roots->isEnabled()) {
        $configuredRoots = $roots->getRoots();
    }
}
```

@package LaravelMCP\MCP\Capabilities

## Methods

### __construct

Create a new client capabilities instance.

Initializes a client capabilities configuration with optional
experimental feature flags and root directory settings. Both
parameters can be null to indicate undefined configurations.

Example:
```php
// Enable experimental features with roots
$capabilities = new ClientCapabilities(
    experimental: true,
    roots: new RootsCapability(enabled: true)
);

// Disable experimental features
$capabilities = new ClientCapabilities(experimental: false);

// Initialize with undefined state
$capabilities = new ClientCapabilities();
```

@param bool|null $experimental Whether experimental features are enabled
                              - true: Enable experimental features
                              - false: Disable experimental features
                              - null: Feature state undefined
@param RootsCapability|null $roots Root directory configuration
                                  - Manages root directory settings
                                  - null: No root configuration

### isExperimental

Check if experimental features are enabled.

Determines whether experimental or beta features should be
available in the client. These features may not be stable
and could change in future releases.

Example:
```php
$experimental = $capabilities->isExperimental();
match ($experimental) {
    true => 'Experimental features enabled',
    false => 'Using stable features only',
    null => 'Feature state undefined'
};
```

@return bool|null True if experimental features are enabled, false if not, null if unknown

### getRoots

Get the root directory configuration.

Retrieves the configuration for root directory management,
including enabled state and configured root directories.
Returns null if root management is not configured.

Example:
```php
if ($roots = $capabilities->getRoots()) {
    if ($roots->isEnabled()) {
        foreach ($roots->getRoots() as $root) {
            echo "Root: {$root['name']} at {$root['path']}\n";
        }
    }
}
```

@return RootsCapability|null The roots capability configuration

### toArray

Convert the capabilities to an array format.

Transforms the client capabilities into a structured array
suitable for storage or transmission. Only includes properties
that have been explicitly set (non-null values).

Example output:
```php
[
    'experimental' => true,           // Only if set
    'roots' => [                      // Only if configured
        'enabled' => true,
        'roots' => [...]
    ]
]
```

@return array The capabilities data as a key-value array

### create

Create a new instance from array data.

Factory method that constructs a ClientCapabilities instance
from a configuration array. This is useful for deserializing
stored configurations or processing API responses.

Example:
```php
$capabilities = ClientCapabilities::create([
    'experimental' => true,
    'roots' => [
        'enabled' => true,
        'roots' => [
            ['path' => '/workspace', 'name' => 'main']
        ]
    ]
]);
```

@param array $data The data to create the instance from, containing:
                   - experimental: bool|null - Feature flag state
                   - roots: array|null - Root directory settings
@return static A new instance of the capabilities

