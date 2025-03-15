# Root

Namespace: ``

Represents a root directory in the MCP system.

A root defines a base directory path that can be used as a reference point
for resolving relative paths and managing file system operations. Roots
help organize and manage file system access in a structured way.

Roots can be used to:
- Define workspace boundaries
- Resolve relative paths
- Manage file system access
- Organize project structure

@package LaravelMCP\MCP

## Methods

### __construct

Create a new root instance.

@param string $uri The URI of the root directory
@param string|null $name Optional name of the root
@throws \InvalidArgumentException If the URI is invalid

### getUri

Get the URI of the root directory.

Returns the full URI of the root directory, including the 'file://' prefix.
This URI can be used for file system operations and path resolution.

@return string The complete URI of the root directory

### getName

Get the name of the root.

Returns the optional name assigned to this root directory.
The name can be used for identification and organization purposes.

@return string|null The root's name, or null if not set

### getPath

Get the filesystem path of the root directory.

Converts the URI to a filesystem path by removing the 'file://' prefix.
This path can be used directly with filesystem operations.

@return string The filesystem path

### toArray

Convert the root to an array representation.

Creates an array containing the root's properties for serialization
or data transfer. The array includes:
- uri: The complete URI of the root directory
- name: The root's name (if set)

@return array The array representation of the root

### create

Create a new root instance from an array.

Factory method that creates a root instance from an array of data.
The array should contain:
- uri: The URI of the root directory (required)
- name: The name of the root (optional)

@param array $data The data to create the root from
@return static A new root instance
@throws \InvalidArgumentException If required data is missing or invalid

### getDescription

Get the description of the root.

This method is part of a common interface pattern but currently
returns null as root descriptions are not implemented.

@return string|null Always returns null

### resolve

Resolve a relative path against this root.

Combines the root's path with a relative path to create a complete
filesystem path. This method:
1. Removes the 'file://' prefix from the root URI
2. Combines it with the relative path
3. Normalizes directory separators
4. Ensures proper path separation

Example:
Root URI: 'file:///var/www'
Relative path: 'app/config.php'
Result: '/var/www/app/config.php'

@param string $relativePath The relative path to resolve
@return string The complete filesystem path

