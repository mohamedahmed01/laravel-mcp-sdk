# PromptsCapability

Namespace: ``

Represents the prompts capability for server-side functionality.

This class manages information about server-side prompt capabilities,
including whether prompt handling is enabled and what prompts are
available on the server.

@package LaravelMCP\MCP\Capabilities\ServerCapabilities

## Methods

### __construct

Create a new prompts capability instance.

@param bool $enabled Whether prompt handling is enabled on the server
@param array $prompts List of available server prompts

### isEnabled

Check if prompt handling is enabled on the server.

@return bool True if prompt handling is enabled, false otherwise

### getPrompts

Get the list of available server prompts.

@return array List of configured server prompts

### toArray

Convert the capability to an array format.

@return array The capability data as a key-value array

### create

Create a new instance from an array of data.

@param array $data The data to create the instance from
@return static A new instance of the capability

