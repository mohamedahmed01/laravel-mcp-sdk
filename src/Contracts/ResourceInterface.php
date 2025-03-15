<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for defining resources in the MCP system.
 *
 * Resources represent data or content that can be accessed through the MCP server.
 * Each resource has a unique URI, content, and optional metadata like MIME type
 * and description. Resources can be static files, dynamic content, or templates
 * that are rendered on demand.
 *
 * Common resource types:
 * - Static files (text, images, etc.)
 * - Dynamic content (generated data)
 * - Templates (rendered with parameters)
 * - API endpoints (returning structured data)
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface ResourceInterface
{
    /**
     * Get the URI of the resource.
     *
     * The URI uniquely identifies the resource within the MCP system and
     * is used for accessing the resource through the server.
     *
     * Examples:
     * - /api/v1/users
     * - /templates/email/welcome
     * - /static/images/logo.png
     *
     * @return string The resource's URI
     */
    public function getUri(): string;

    /**
     * Get the content of the resource.
     *
     * The content can be any type of data that the resource represents,
     * such as text, binary data, or structured data.
     *
     * Common content types:
     * - string: Text content, HTML, etc.
     * - array: Structured data, JSON-like content
     * - resource: File handles, streams
     * - object: Custom data structures
     *
     * @return mixed The resource's content
     */
    public function getContent(): mixed;

    /**
     * Get the MIME type of the resource.
     *
     * The MIME type indicates the format of the resource's content,
     * helping clients understand how to interpret the data.
     *
     * Common MIME types:
     * - text/plain: Plain text
     * - application/json: JSON data
     * - text/html: HTML content
     * - image/png: PNG images
     *
     * @return string|null The resource's MIME type or null if not set
     */
    public function getMimeType(): ?string;

    /**
     * Get the description of the resource.
     *
     * The description provides additional information about the resource,
     * its purpose, and how it should be used. This can include:
     * - Purpose of the resource
     * - Expected usage patterns
     * - Required parameters
     * - Access requirements
     *
     * @return string|null The resource's description or null if not set
     */
    public function getDescription(): ?string;

    /**
     * Handle a request for this resource.
     *
     * This method is called when a client requests the resource. It can
     * perform any necessary processing or transformation of the resource's
     * content before returning it.
     *
     * Common handling tasks:
     * - Content generation
     * - Data transformation
     * - Access control
     * - Parameter validation
     * - Error handling
     *
     * @return mixed The processed resource content
     * @throws \RuntimeException If resource handling fails
     */
    public function handle(): mixed;
}
