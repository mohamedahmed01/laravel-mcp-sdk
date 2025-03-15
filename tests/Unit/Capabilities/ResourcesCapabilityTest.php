<?php

namespace Tests\Unit\Capabilities;

use LaravelMCP\MCP\Capabilities\ResourcesCapability;
use Tests\TestCase;

class ResourcesCapabilityTest extends TestCase
{
    public function testConstructor(): void
    {
        $capability = new ResourcesCapability(true, true);
        $this->assertInstanceOf(ResourcesCapability::class, $capability);
    }

    public function testGetSubscribe(): void
    {
        $capability = new ResourcesCapability(true, false);
        $this->assertTrue($capability->getSubscribe());

        $capability = new ResourcesCapability(false, true);
        $this->assertFalse($capability->getSubscribe());

        $capability = new ResourcesCapability();
        $this->assertNull($capability->getSubscribe());
    }

    public function testGetListChanged(): void
    {
        $capability = new ResourcesCapability(false, true);
        $this->assertTrue($capability->getListChanged());

        $capability = new ResourcesCapability(true, false);
        $this->assertFalse($capability->getListChanged());

        $capability = new ResourcesCapability();
        $this->assertNull($capability->getListChanged());
    }

    public function testToArray(): void
    {
        $capability = new ResourcesCapability(true, true);
        $this->assertEquals([
            'subscribe' => true,
            'listChanged' => true,
        ], $capability->toArray());

        $capability = new ResourcesCapability(false, false);
        $this->assertEquals([
            'subscribe' => false,
            'listChanged' => false,
        ], $capability->toArray());

        $capability = new ResourcesCapability();
        $this->assertEquals([], $capability->toArray());

        $capability = new ResourcesCapability(true);
        $this->assertEquals(['subscribe' => true], $capability->toArray());

        $capability = new ResourcesCapability(null, true);
        $this->assertEquals(['listChanged' => true], $capability->toArray());
    }

    public function testCreate(): void
    {
        $capability = ResourcesCapability::create([
            'subscribe' => true,
            'listChanged' => true,
        ]);
        $this->assertTrue($capability->getSubscribe());
        $this->assertTrue($capability->getListChanged());

        $capability = ResourcesCapability::create([
            'subscribe' => false,
            'listChanged' => false,
        ]);
        $this->assertFalse($capability->getSubscribe());
        $this->assertFalse($capability->getListChanged());

        $capability = ResourcesCapability::create([]);
        $this->assertNull($capability->getSubscribe());
        $this->assertNull($capability->getListChanged());
    }

    public function testCreateWithNullValues(): void
    {
        $capability = ResourcesCapability::create([
            'subscribe' => null,
            'listChanged' => null,
        ]);
        $this->assertNull($capability->getSubscribe());
        $this->assertNull($capability->getListChanged());
    }
}
