<?php

namespace Tests\Unit\Capabilities\ServerCapabilities;

use LaravelMCP\MCP\Capabilities\ServerCapabilities\PromptsCapability;
use Tests\TestCase;

class PromptsCapabilityTest extends TestCase
{
    public function testConstructor(): void
    {
        $capability = new PromptsCapability(true, ['test-prompt']);
        $this->assertInstanceOf(PromptsCapability::class, $capability);
    }

    public function testIsEnabled(): void
    {
        $capability = new PromptsCapability(true, ['test-prompt']);
        $this->assertTrue($capability->isEnabled());

        $capability = new PromptsCapability(false, []);
        $this->assertFalse($capability->isEnabled());
    }

    public function testGetPrompts(): void
    {
        $prompts = ['test-prompt-1', 'test-prompt-2'];
        $capability = new PromptsCapability(true, $prompts);
        $this->assertEquals($prompts, $capability->getPrompts());
    }

    public function testToArray(): void
    {
        $prompts = ['test-prompt-1', 'test-prompt-2'];
        $capability = new PromptsCapability(true, $prompts);

        $expected = [
            'enabled' => true,
            'prompts' => $prompts,
        ];

        $this->assertEquals($expected, $capability->toArray());
    }

    public function testCreate(): void
    {
        $data = [
            'enabled' => true,
            'prompts' => ['test-prompt'],
        ];

        $capability = PromptsCapability::create($data);

        $this->assertInstanceOf(PromptsCapability::class, $capability);
        $this->assertTrue($capability->isEnabled());
        $this->assertEquals(['test-prompt'], $capability->getPrompts());
    }
}
