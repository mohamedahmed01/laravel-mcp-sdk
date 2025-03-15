<?php

namespace Tests\Unit\Capabilities;

use LaravelMCP\MCP\Capabilities\PromptsCapability;
use Tests\TestCase;

class PromptsCapabilityTest extends TestCase
{
    public function testConstructor(): void
    {
        $capability = new PromptsCapability(true);
        $this->assertInstanceOf(PromptsCapability::class, $capability);
    }

    public function testGetListChanged(): void
    {
        $capability = new PromptsCapability(true);
        $this->assertTrue($capability->getListChanged());

        $capability = new PromptsCapability(false);
        $this->assertFalse($capability->getListChanged());

        $capability = new PromptsCapability();
        $this->assertNull($capability->getListChanged());
    }

    public function testToArray(): void
    {
        $capability = new PromptsCapability(true);
        $this->assertEquals(['listChanged' => true], $capability->toArray());

        $capability = new PromptsCapability(false);
        $this->assertEquals(['listChanged' => false], $capability->toArray());

        $capability = new PromptsCapability();
        $this->assertEquals([], $capability->toArray());
    }

    public function testCreate(): void
    {
        $capability = PromptsCapability::create(['listChanged' => true]);
        $this->assertTrue($capability->getListChanged());

        $capability = PromptsCapability::create(['listChanged' => false]);
        $this->assertFalse($capability->getListChanged());

        $capability = PromptsCapability::create([]);
        $this->assertNull($capability->getListChanged());
    }

    public function testCreateWithNullListChanged(): void
    {
        $capability = PromptsCapability::create(['listChanged' => null]);
        $this->assertNull($capability->getListChanged());
    }
}
