<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for handling requests in the MCP system.
 *
 * A request represents a message or command sent to the MCP server that
 * requires processing and a response. Requests can be tool calls, resource
 * access, prompt handling, or any other interaction that expects a result.
 *
 * Requests can be used to:
 * - Execute tool commands
 * - Access resources
 * - Process prompts
 * - Handle client-server interactions
 * - Query system state
 * - Perform batch operations
 * - Manage configurations
 *
 * Each request is processed synchronously and returns a response that
 * indicates success or failure along with any relevant data.
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface RequestInterface
{
    /**
     * Get the type of the request.
     *
     * The type identifies what kind of request this is and determines
     * how it should be handled by the server.
     *
     * Common request types:
     * - tool_call: Execute a specific tool
     * - resource_request: Access or modify a resource
     * - prompt_request: Process a prompt
     * - query: Retrieve system information
     * - batch: Execute multiple operations
     * - config: Modify system configuration
     *
     * @return string The request type identifier
     */
    public function getType(): string;

    /**
     * Get the arguments associated with the request.
     *
     * Arguments provide the data needed to process the request, such as
     * tool parameters, resource identifiers, or prompt content.
     *
     * Common argument structures:
     * Tool Call:
     * {
     *     "name": string,     // Tool name
     *     "arguments": array  // Tool-specific parameters
     * }
     *
     * Resource Request:
     * {
     *     "uri": string,      // Resource identifier
     *     "action": string,   // Action to perform
     *     "data": mixed      // Action-specific data
     * }
     *
     * Prompt Request:
     * {
     *     "name": string,     // Prompt identifier
     *     "messages": array,  // Prompt messages
     *     "options": array   // Processing options
     * }
     *
     * @return array The request arguments
     */
    public function getArguments(): array;
}
