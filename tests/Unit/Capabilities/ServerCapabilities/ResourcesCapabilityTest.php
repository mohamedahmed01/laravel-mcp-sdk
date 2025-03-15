<?php

namespace Tests\Unit\Capabilities\ServerCapabilities;

use LaravelMCP\MCP\Capabilities\ServerCapabilities\ResourcesCapability;
use Tests\TestCase;

class ResourcesCapabilityTest extends TestCase
{
    public function testConstructor(): void
    {
        $capability = new ResourcesCapability(true, ['test-resource']);
        $this->assertInstanceOf(ResourcesCapability::class, $capability);
    }

    public function testIsEnabled(): void
    {
        $capability = new ResourcesCapability(true, ['test-resource']);
        $this->assertTrue($capability->isEnabled());

        $capability = new ResourcesCapability(false, []);
        $this->assertFalse($capability->isEnabled());
    }

    public function testGetResources(): void
    {
        $resources = ['test-resource-1', 'test-resource-2'];
        $capability = new ResourcesCapability(true, $resources);
        $this->assertEquals($resources, $capability->getResources());
    }

    public function testToArray(): void
    {
        $resources = ['test-resource-1', 'test-resource-2'];
        $capability = new ResourcesCapability(true, $resources);

        $expected = [
            'enabled' => true,
            'resources' => $resources,
        ];

        $this->assertEquals($expected, $capability->toArray());
    }

    public function testCreate(): void
    {
        $data = [
            'enabled' => true,
            'resources' => ['test-resource'],
        ];

        $capability = ResourcesCapability::create($data);

        $this->assertInstanceOf(ResourcesCapability::class, $capability);
        $this->assertTrue($capability->isEnabled());
        $this->assertEquals(['test-resource'], $capability->getResources());
    }
}
