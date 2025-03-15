<?php

namespace Tests\Unit\Capabilities;

use LaravelMCP\MCP\Capabilities\ClientCapabilities;
use LaravelMCP\MCP\Capabilities\PromptsCapability;
use LaravelMCP\MCP\Capabilities\ResourcesCapability;
use LaravelMCP\MCP\Capabilities\RootsCapability;
use LaravelMCP\MCP\Capabilities\ServerCapabilities;
use LaravelMCP\MCP\Capabilities\ToolsCapability;
use stdClass;
use Tests\TestCase;

class CapabilitiesTest extends TestCase
{
    public function testClientCapabilities()
    {
        $roots = new RootsCapability(true, ['root1', 'root2']);
        $capabilities = new ClientCapabilities(true, $roots);

        $this->assertTrue($capabilities->isExperimental());
        $this->assertInstanceOf(RootsCapability::class, $capabilities->getRoots());
        $this->assertEquals([
            'experimental' => true,
            'roots' => [
                'enabled' => true,
                'roots' => ['root1', 'root2'],
            ],
        ], $capabilities->toArray());
    }

    public function testClientCapabilitiesCreatesFromArray()
    {
        $data = [
            'experimental' => true,
            'roots' => [
                'enabled' => true,
                'roots' => ['root1', 'root2'],
            ],
        ];

        $capabilities = ClientCapabilities::create($data);

        $this->assertTrue($capabilities->isExperimental());
        $this->assertInstanceOf(RootsCapability::class, $capabilities->getRoots());
        $this->assertEquals($data, $capabilities->toArray());
    }

    public function testServerCapabilities()
    {
        $prompts = new PromptsCapability(true);
        $resources = new ResourcesCapability(true, true);
        $tools = new ToolsCapability(true);
        $logging = new stdClass();
        $logging->enabled = true;
        $logging->level = 'debug';

        $capabilities = new ServerCapabilities(
            experimental: ['enabled' => true],
            logging: $logging,
            prompts: $prompts,
            resources: $resources,
            tools: $tools
        );

        $this->assertEquals(['enabled' => true], $capabilities->getExperimental());
        $this->assertInstanceOf(stdClass::class, $capabilities->getLogging());
        $this->assertTrue($capabilities->getLogging()->enabled);
        $this->assertEquals('debug', $capabilities->getLogging()->level);
        $this->assertInstanceOf(PromptsCapability::class, $capabilities->getPrompts());
        $this->assertInstanceOf(ResourcesCapability::class, $capabilities->getResources());
        $this->assertInstanceOf(ToolsCapability::class, $capabilities->getTools());
        $this->assertEquals([
            'experimental' => ['enabled' => true],
            'logging' => $logging,
            'prompts' => [
                'listChanged' => true,
            ],
            'resources' => [
                'subscribe' => true,
                'listChanged' => true,
            ],
            'tools' => [
                'listChanged' => true,
            ],
        ], $capabilities->toArray());
    }

    public function testServerCapabilitiesCreatesFromArray()
    {
        $logging = new stdClass();
        $logging->enabled = true;
        $logging->level = 'debug';

        $data = [
            'experimental' => ['enabled' => true],
            'logging' => $logging,
            'prompts' => [
                'listChanged' => true,
            ],
            'resources' => [
                'subscribe' => true,
                'listChanged' => true,
            ],
            'tools' => [
                'listChanged' => true,
            ],
        ];

        $capabilities = ServerCapabilities::create($data);

        $this->assertEquals(['enabled' => true], $capabilities->getExperimental());
        $this->assertInstanceOf(stdClass::class, $capabilities->getLogging());
        $this->assertTrue($capabilities->getLogging()->enabled);
        $this->assertEquals('debug', $capabilities->getLogging()->level);
        $this->assertInstanceOf(PromptsCapability::class, $capabilities->getPrompts());
        $this->assertInstanceOf(ResourcesCapability::class, $capabilities->getResources());
        $this->assertInstanceOf(ToolsCapability::class, $capabilities->getTools());
        $this->assertEquals($data, $capabilities->toArray());
    }

    public function testCapabilitiesWithNullValues()
    {
        $clientCapabilities = new ClientCapabilities();
        $this->assertNull($clientCapabilities->isExperimental());
        $this->assertNull($clientCapabilities->getRoots());
        $this->assertEquals([], $clientCapabilities->toArray());

        $serverCapabilities = new ServerCapabilities();
        $this->assertNull($serverCapabilities->getExperimental());
        $this->assertNull($serverCapabilities->getLogging());
        $this->assertNull($serverCapabilities->getPrompts());
        $this->assertNull($serverCapabilities->getResources());
        $this->assertNull($serverCapabilities->getTools());
        $this->assertEquals([], $serverCapabilities->toArray());
    }
}
