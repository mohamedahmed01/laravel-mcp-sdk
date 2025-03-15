<?php

namespace LaravelMCP\MCP\Contracts;

/**
 * Interface for defining prompts in the MCP system.
 *
 * Prompts represent interactive elements that can be presented to users or
 * processed by the system. Each prompt has a name, a set of messages that
 * define its content, and optional metadata. Prompts can be used for user
 * input, system notifications, or structured communication between components.
 *
 * Common prompt types:
 * - User input forms
 * - Confirmation dialogs
 * - System notifications
 * - Interactive wizards
 * - Data collection forms
 *
 * @package LaravelMCP\MCP\Contracts
 */
interface PromptInterface
{
    /**
     * Get the name of the prompt.
     *
     * The name uniquely identifies the prompt within the MCP system and
     * is used for referencing and invoking the prompt.
     *
     * Example names:
     * - user_registration
     * - confirm_deletion
     * - system_notification
     * - data_collection
     *
     * @return string The prompt's unique identifier
     */
    public function getName(): string;

    /**
     * Get the messages associated with the prompt.
     *
     * Messages form the content of the prompt and can include instructions,
     * questions, or any other information relevant to the prompt's purpose.
     *
     * Message structure:
     * [
     *     [
     *         'role' => 'system|user|assistant',
     *         'content' => 'Message content',
     *         'type' => 'text|html|markdown',
     *         'metadata' => ['key' => 'value']
     *     ],
     *     ...
     * ]
     *
     * @return array The array of messages that make up the prompt
     */
    public function getMessages(): array;

    /**
     * Get the description of the prompt.
     *
     * The description provides additional information about the prompt,
     * its purpose, and how it should be used. A good description includes:
     * - Purpose of the prompt
     * - Expected user interaction
     * - Required input format
     * - Response format
     * - Example usage
     *
     * @return string|null The prompt's description or null if not set
     */
    public function getDescription(): ?string;

    /**
     * Handle the prompt with the given arguments.
     *
     * This method is called when the prompt is invoked. It processes the
     * provided arguments and returns an appropriate response based on the
     * prompt's logic.
     *
     * The handling process:
     * 1. Validates input arguments
     * 2. Processes the prompt's messages
     * 3. Applies any transformations
     * 4. Generates the response
     *
     * @param array $arguments The arguments to process
     * @return array The response from handling the prompt
     * @throws \InvalidArgumentException If arguments are invalid
     * @throws \RuntimeException If prompt handling fails
     */
    public function handle(array $arguments): array;

    /**
     * Get the arguments for this prompt.
     *
     * Returns the configuration of expected arguments for this prompt.
     * Arguments can include:
     * - Input parameters
     * - Configuration options
     * - Processing flags
     * - Custom metadata
     *
     * @return array<string, mixed> The prompt's argument configuration
     */
    public function getArguments(): array;

    /**
     * Get the handler function for this prompt.
     *
     * The handler function processes the prompt's messages and arguments
     * to generate a response. It should:
     * - Accept an array of arguments
     * - Process the prompt's messages
     * - Return a structured response
     *
     * Handler signature:
     * function(array $arguments): array
     *
     * @return callable The handler function
     */
    public function getHandler(): callable;
}
