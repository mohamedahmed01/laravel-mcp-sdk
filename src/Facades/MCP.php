<?php

namespace LaravelMCP\MCP\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelMCP\MCP\Contracts\PromptInterface;
use LaravelMCP\MCP\Contracts\ResourceInterface;
use LaravelMCP\MCP\Contracts\ResourceTemplateInterface;
use LaravelMCP\MCP\Contracts\ToolInterface;

/**
 * @method static void addTool(string $name, callable $handler, ?string $description = null)
 * @method static void addToolInterface(ToolInterface $tool)
 * @method static void addResource(string $uri, mixed $content, ?string $mimeType = null, ?string $description = null)
 * @method static void addResourceInterface(ResourceInterface $resource)
 * @method static void addResourceTemplate(ResourceTemplateInterface $template)
 * @method static void addPrompt(string $name, array $messages, ?string $description = null)
 * @method static void addPromptInterface(PromptInterface $prompt)
 */
class MCP extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mcp';
    }
}
