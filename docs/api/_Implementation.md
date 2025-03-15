# Implementation

Namespace: ``

Base class for MCP system implementations.

This class provides a foundation for implementing various components
in the MCP system. It includes common functionality and utilities
that can be shared across different implementations.

The class supports:
- Serialization to array format
- Creation from array data
- Common implementation patterns

@package LaravelMCP\MCP

## Methods

### __construct

Create a new implementation instance.

@param string $name The name of the implementation
@param string $version The version identifier of the implementation

### getName

Get the implementation's name.

Returns the name that identifies this implementation.
This is typically used for registration and lookup purposes.

@return string The implementation name

### getVersion

Get the implementation's version.

Returns the version identifier of this implementation.
This can be used for version checking and compatibility.

@return string The version identifier

### toArray

Convert the implementation to an array format.

Creates an array containing the implementation's core properties.
The base implementation includes:
- name: The implementation name
- version: The version identifier

Child classes can override this method to include additional
properties specific to their implementation.

@return array The implementation data as a key-value array

### create

Create a new instance from an array of data.

Factory method that creates an implementation instance from
an array of data. The array must contain:
- name: The implementation name
- version: The version identifier

@param array $data The data to create the instance from
@return static A new instance of the implementation
@throws \InvalidArgumentException If required data is missing

