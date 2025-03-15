<?php

namespace LaravelMCP\MCP\Server;

use LaravelMCP\MCP\Contracts\ResourceInterface;

/**
 * Implementation of a resource in the MCP system.
 *
 * A resource represents a piece of content that can be accessed through the
 * MCP server. Resources can be static files, dynamic content, or data that
 * is generated on demand. Each resource has a unique URI, content handler,
 * and optional metadata like MIME type and description.
 *
 * Resources can be used to:
 * - Serve static files
 * - Generate dynamic content
 * - Provide access to data stores
 * - Implement API endpoints
 *
 * @package LaravelMCP\MCP\Server
 */
class Resource implements ResourceInterface
{
    /**
     * @var string The unique URI identifier of the resource
     */
    private string $uri;

    /**
     * @var callable The function that provides the resource's content
     */
    private $handler;

    /**
     * @var string|null The MIME type of the resource's content
     */
    private ?string $mimeType;

    /**
     * @var string|null Description of the resource's purpose and usage
     */
    private ?string $description;

    /**
     * Create a new resource instance.
     *
     * @param string $uri The unique URI identifier for the resource
     * @param callable $handler The function that provides the resource's content
     * @param string|null $mimeType Optional MIME type of the content
     * @param string|null $description Optional description of the resource
     */
    public function __construct(string $uri, callable $handler, ?string $mimeType = null, ?string $description = null)
    {
        $this->uri = $uri;
        $this->handler = $handler;
        $this->mimeType = $mimeType;
        $this->description = $description;
    }

    /**
     * Get the resource's URI.
     *
     * The URI uniquely identifies this resource within the MCP server.
     * It is used for routing requests and accessing the resource.
     *
     * @return string The resource's URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the resource's content.
     *
     * Retrieves the content by invoking the resource's handler function.
     * The content can be any type that the handler returns.
     *
     * @return mixed The resource's content
     */
    public function getContent(): mixed
    {
        $content = ($this->handler)();

        return is_array($content) ? $content : [$content];
    }

    /**
     * Get the resource's MIME type.
     *
     * The MIME type indicates the format of the resource's content.
     * This is useful for HTTP responses and content negotiation.
     *
     * @return string|null The MIME type, or null if not specified
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * Get the resource's description.
     *
     * The description provides information about the resource's purpose,
     * usage, and any special considerations.
     *
     * @return string|null The description, or null if not specified
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Handle a request to this resource.
     *
     * This is the main entry point for accessing the resource's content.
     * It invokes the handler function and returns its result.
     * Unlike getContent(), this method is intended for request handling
     * and may include additional processing in the future.
     *
     * @return mixed The result of handling the request
     */
    public function handle(): mixed
    {
        return $this->getContent();
    }

    /**
     * Get the resource's name.
     *
     * The name is derived from the resource's URI and uniquely identifies
     * the resource within the MCP server.
     *
     * @return string The resource's name (same as its URI)
     */
    public function getName(): string
    {
        return $this->uri;
    }
}
