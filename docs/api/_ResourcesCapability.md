# ResourcesCapability

Namespace: ``

Represents the resources capability for server-side functionality.

This class manages information about server-side resource capabilities,
including whether resource handling is enabled and what resources are
available on the server.

@package LaravelMCP\MCP\Capabilities\ServerCapabilities

## Methods

### __construct

Create a new resources capability instance.

@param bool $enabled Whether resource handling is enabled on the server
@param array $resources List of available server resources

### isEnabled

Check if resource handling is enabled on the server.

@return bool True if resource handling is enabled, false otherwise

### getResources

Get the list of available server resources.

@return array List of configured server resources

### toArray

Convert the capability to an array format.

@return array The capability data as a key-value array

### create

Create a new instance from an array of data.

@param array $data The data to create the instance from
@return static A new instance of the capability

