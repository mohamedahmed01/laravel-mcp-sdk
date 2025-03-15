<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for defining tools in the MCP system.
 *
 * Tools are the primary way to extend the MCP server's functionality. Each tool
 * represents a specific capability that can be invoked by clients. Tools can
 * perform various operations like file manipulation, system commands, or custom
 * business logic.
 *
 * Common tool types:
 * - File operations (read, write, delete)
 * - System commands (execute, process management)
 * - Data manipulation (transform, validate)
 * - External API integration
 * - Custom business logic
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface ToolInterface
{
    /**
     * Get the name of the tool.
     *
     * The name serves as a unique identifier for the tool within the MCP system.
     * It should be:
     * - Descriptive of the tool's purpose
     * - Lowercase with underscores
     * - Unique within the system
     *
     * Example names:
     * - file_reader
     * - system_command
     * - data_transformer
     *
     * @return string The unique identifier of the tool
     */
    public function getName(): string;

    /**
     * Get the handler function for the tool.
     *
     * The handler is a callable that implements the tool's core functionality.
     * It receives the arguments passed during tool invocation and returns
     * the result of the operation.
     *
     * Handler signature:
     * function(array $arguments): array
     *
     * The handler should:
     * - Validate input arguments
     * - Perform the tool's operation
     * - Return results in a consistent format
     * - Handle errors appropriately
     *
     * @return callable The function that implements the tool's logic
     */
    public function getHandler(): callable;

    /**
     * Get the description of the tool.
     *
     * The description should provide clear information about what the tool does,
     * its purpose, and how it should be used. A good description includes:
     * - Main functionality
     * - Use cases
     * - Required permissions or prerequisites
     * - Expected inputs and outputs
     * - Example usage
     *
     * @return string|null The tool's description or null if not set
     */
    public function getDescription(): ?string;

    /**
     * Get the parameters configuration for the tool.
     *
     * Parameters define the expected input format and validation rules for
     * the tool's arguments. This helps ensure that the tool receives correctly
     * formatted data.
     *
     * Parameter configuration format:
     * [
     *     'name' => [
     *         'type' => 'string|int|bool|array',
     *         'required' => true|false,
     *         'description' => 'Parameter description',
     *         'default' => 'Default value',
     *         'validation' => ['rule1', 'rule2']
     *     ],
     *     ...
     * ]
     *
     * @return array The tool's parameter configuration
     */
    public function getParameters(): array;

    /**
     * Set the parameters configuration for the tool.
     *
     * Updates the tool's parameter configuration with new validation rules
     * and requirements. This allows dynamic modification of the tool's
     * input requirements.
     *
     * @param array $parameters The parameter configuration to set
     * @return void
     * @throws \InvalidArgumentException If the parameter configuration is invalid
     */
    public function setParameters(array $parameters): void;

    /**
     * Handle a tool invocation with the provided arguments.
     *
     * This method is called when the tool is invoked by a client. It:
     * 1. Validates the input arguments against the parameter configuration
     * 2. Executes the tool's handler function with the arguments
     * 3. Returns the result in a standardized format
     *
     * @param array $arguments The arguments passed to the tool
     * @return array The result of the tool's operation
     * @throws \InvalidArgumentException If the arguments are invalid
     * @throws \RuntimeException If the tool operation fails
     */
    public function handle(array $arguments): array;
}
