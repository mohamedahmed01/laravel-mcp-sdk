<?php

namespace LaravelMCP\MCP\Server;

use LaravelMCP\MCP\Contracts\PromptInterface;

/**
 * Implementation of a prompt in the MCP system.
 *
 * A prompt represents an interactive element that can be presented to users
 * or processed by the system. Each prompt has a unique name, a handler that
 * processes requests, and a set of messages that define its content.
 *
 * Prompts can be used to:
 * - Gather user input
 * - Display system notifications
 * - Process structured messages
 * - Implement interactive workflows
 *
 * @package LaravelMCP\MCP\Server
 */
class Prompt implements PromptInterface
{
    /**
     * @var string The unique identifier of the prompt
     */
    private string $name;

    /**
     * @var callable The function that processes prompt requests
     */
    private $handler;

    /**
     * @var string|null Description of the prompt's purpose and usage
     */
    private ?string $description;

    /**
     * @var array The messages that make up the prompt's content
     */
    private array $messages = [];

    /**
     * @var array The default arguments for the prompt
     */
    private array $defaultArguments = [];

    /**
     * Create a new prompt instance.
     *
     * @param string $name The unique identifier for the prompt
     * @param callable $handler The function that processes prompt requests
     * @param string|null $description Optional description of the prompt
     */
    public function __construct(string $name, callable $handler, ?string $description = null)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the default arguments for the prompt.
     *
     * @param array $arguments The default arguments
     */
    public function setDefaultArguments(array $arguments): void
    {
        $this->defaultArguments = $arguments;
    }

    /**
     * Get the default arguments for the prompt.
     *
     * @return array The default arguments
     */
    public function getDefaultArguments(): array
    {
        return $this->defaultArguments;
    }

    /**
     * Handle a prompt request.
     *
     * @param array $arguments The arguments for the request
     * @return array The response data
     */
    public function handle(array $arguments): array
    {
        $mergedArguments = array_merge($this->defaultArguments, $arguments);

        return ($this->handler)($mergedArguments);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(): array
    {
        return $this->messages;
    }

    /**
     * Set the messages for this prompt.
     *
     * @param array $messages The messages that make up the prompt's content
     */
    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    /**
     * Get the handler function for this prompt.
     *
     * @return callable The handler function
     */
    public function getHandler(): callable
    {
        return $this->handler;
    }
}
