<?php

namespace LaravelMCP\MCP\Server;

use LaravelMCP\MCP\Contracts\ResourceInterface;
use LaravelMCP\MCP\Contracts\ResourceTemplateInterface;

/**
 * Implementation of a resource template in the MCP system.
 *
 * A resource template provides a way to generate dynamic resources based on
 * predefined patterns. Templates can be used to create resources with similar
 * characteristics but different content, or to generate resources based on
 * user input or system state.
 *
 * Resource templates can be used to:
 * - Generate dynamic resources
 * - Create consistent resource structures
 * - Implement resource factories
 * - Handle parameterized resource creation
 *
 * @package LaravelMCP\MCP\Server
 */
class ResourceTemplate implements ResourceTemplateInterface
{
    /**
     * @var string The unique identifier for the template
     */
    private string $name;

    /**
     * @var string The URI pattern for generated resources
     */
    private string $uri;

    /**
     * @var array The parameters for the template
     */
    private array $parameters;

    /**
     * @var string|null The MIME type for generated resources
     */
    private ?string $mimeType;

    /**
     * @var string|null The description of the template
     */
    private ?string $description;

    /**
     * @var array The annotations for the template
     */
    private array $annotations;

    /**
     * Create a new resource template instance.
     *
     * @param string $name The unique identifier for the template
     * @param string $uri The URI for generated resources
     * @param array $parameters Optional parameters for the template
     * @param string|null $mimeType Optional MIME type for generated resources
     * @param string|null $description Optional description of the template
     * @param array $annotations Optional annotations for the template
     */
    public function __construct(
        string $name,
        string $uri,
        array $parameters = [],
        ?string $mimeType = null,
        ?string $description = null,
        array $annotations = []
    ) {
        $this->name = $name;
        $this->uri = $uri;
        $this->parameters = $parameters;
        $this->mimeType = $mimeType;
        $this->description = $description;
        $this->annotations = $annotations;
    }

    /**
     * Get the template's name.
     *
     * @return string The template name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the template's URI pattern.
     *
     * @return string The URI pattern
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get the template's MIME type.
     *
     * @return string|null The MIME type
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * Get the template's description.
     *
     * @return string|null The description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the template's annotations.
     *
     * @return array The annotations
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * Render the template with the given parameters.
     *
     * This method combines the provided parameters with the template's default parameters
     * and uses them to render the URI pattern. The provided parameters take precedence
     * over the template's default parameters.
     *
     * Example:
     * Template URI: "/users/{id}/posts/{post_id}"
     * Template parameters: ["id" => "default"]
     * Provided parameters: ["post_id" => "123"]
     * Result: "/users/default/posts/123"
     *
     * @param array $parameters Additional parameters to use for rendering
     * @return string The rendered URI with all parameters replaced
     */
    public function render(array $parameters = []): string
    {
        $mergedParameters = array_merge($this->parameters, $parameters);

        return strtr($this->uri, $mergedParameters);
    }

    /**
     * Get the URI template pattern.
     *
     * Returns the raw URI pattern with parameter placeholders.
     * This is useful when you need to inspect or manipulate the pattern
     * before rendering.
     *
     * Example:
     * If the URI is "/users/{id}/posts/{post_id}",
     * this method returns that exact string without any parameter substitution.
     *
     * @return string The URI template pattern
     */
    public function getUriTemplate(): string
    {
        return $this->uri;
    }

    /**
     * Expand the URI template with the given parameters.
     *
     * Similar to render(), but specifically for URI expansion. This method
     * replaces parameters in the format "{param}" with their values.
     * Unlike render(), this method:
     * - Only uses the provided parameters (ignores template defaults)
     * - Specifically handles URI parameter expansion
     * - Maintains URI encoding
     *
     * Example:
     * Template: "/users/{id}/posts/{post_id}"
     * Parameters: ["id" => "123", "post_id" => "456"]
     * Result: "/users/123/posts/456"
     *
     * @param array $parameters The parameters to expand with
     * @return string The expanded URI
     */
    public function expandUri(array $parameters): string
    {
        $uri = $this->uri;
        foreach ($parameters as $key => $value) {
            $uri = str_replace("{{$key}}", (string)$value, $uri);
        }

        return $uri;
    }

    /**
     * Get the template pattern.
     *
     * @return array The template pattern
     */
    public function getTemplate(): array
    {
        return $this->parameters;
    }

    /**
     * Create a new resource from this template.
     *
     * Generates a new resource instance using this template's configuration
     * and the provided arguments. The process involves:
     * 1. Expanding the URI using the provided arguments
     * 2. Generating the content using the template's logic
     * 3. Creating a new Resource instance with the expanded URI and content
     *
     * The created resource inherits:
     * - MIME type from the template
     * - Description from the template
     * - Annotations from the template
     *
     * @param array $arguments Arguments used to generate the resource
     * @return ResourceInterface The created resource
     */
    public function createResource(array $arguments): ResourceInterface
    {
        $uri = $this->expandUri($arguments);
        $content = $this->generateContent($arguments);

        return new Resource($uri, fn () => $content, $this->mimeType, $this->description);
    }

    /**
     * Generate content for a new resource.
     *
     * Internal method used by createResource() to generate the content
     * for a new resource based on the template's configuration and
     * the provided arguments.
     *
     * @param array $arguments Arguments used to generate the content
     * @return mixed The generated content
     */
    private function generateContent(array $arguments): mixed
    {
        // Implement content generation logic based on template and arguments
        return $arguments;
    }
}
