<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for defining resource templates in the MCP system.
 *
 * Resource templates provide a way to generate dynamic resources based on
 * predefined patterns or structures. They can be used to create resources
 * with similar characteristics but different content, or to generate
 * resources based on user input or system state.
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface ResourceTemplateInterface
{
    /**
     * Get the name of the resource template.
     *
     * The name uniquely identifies the template within the MCP system and
     * is used for referencing and applying the template.
     *
     * @return string The template's unique identifier
     */
    public function getName(): string;

    /**
     * Get the URI template for the resource.
     *
     * The URI template defines the pattern for generating resource URIs.
     * It can include placeholders that are replaced with actual values
     * when the template is used.
     *
     * Example templates:
     * - /api/v1/users/{id}
     * - /reports/{year}/{month}
     * - /templates/{type}/{name}
     *
     * @return string The URI template pattern
     */
    public function getUriTemplate(): string;

    /**
     * Get the URI for this resource template.
     *
     * @return string The URI that identifies this template
     */
    public function getUri(): string;

    /**
     * Render the resource template.
     *
     * @return mixed The rendered resource content
     */
    public function render(): mixed;

    /**
     * Get the MIME type for resources matching this template.
     *
     * Specifies the content type of resources generated from this template.
     * This helps clients understand how to handle the resource content.
     *
     * Common MIME types:
     * - application/json
     * - text/html
     * - text/plain
     * - application/xml
     *
     * @return string|null The MIME type or null if not specified
     */
    public function getMimeType(): ?string;

    /**
     * Get the description of the resource template.
     *
     * The description provides additional information about the template,
     * its purpose, and how it should be used to generate resources.
     *
     * @return string|null The template's description or null if not set
     */
    public function getDescription(): ?string;

    /**
     * Get annotations for the resource template.
     *
     * Annotations provide metadata about the template that can be used for:
     * - Documentation generation
     * - Parameter validation
     * - Code generation
     * - Template processing
     *
     * @return array The template's annotations
     */
    public function getAnnotations(): array;

    /**
     * Expand the URI template with the given parameters.
     *
     * Replaces placeholders in the URI template with actual values from
     * the parameters array. This creates a concrete URI that can be used
     * to access the generated resource.
     *
     * Example:
     * Template: /api/v1/users/{id}
     * Parameters: ['id' => 123]
     * Result: /api/v1/users/123
     *
     * @param array $parameters Values to substitute in the template
     * @return string The expanded URI
     * @throws \InvalidArgumentException If required parameters are missing
     */
    public function expandUri(array $parameters): string;

    /**
     * Get the template for the resource.
     *
     * Returns the raw template definition that defines how resources
     * should be generated. This can include:
     * - Content patterns
     * - Variable definitions
     * - Processing instructions
     * - Validation rules
     *
     * @return array The template definition
     */
    public function getTemplate(): array;

    /**
     * Create a new resource using this template.
     *
     * This method generates a new resource based on the template's pattern
     * and the provided arguments. The resulting resource should conform to
     * the ResourceInterface contract.
     *
     * @param array $arguments Arguments used to customize the generated resource
     * @return ResourceInterface The newly created resource
     */
    public function createResource(array $arguments): ResourceInterface;
}
