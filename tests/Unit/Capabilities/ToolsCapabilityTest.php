<?php

namespace Tests\Unit\Capabilities;

use LaravelMCP\MCP\Capabilities\ToolsCapability;
use Tests\TestCase;

class ToolsCapabilityTest extends TestCase
{
    public function testCanBeInstantiatedWithNullListChanged(): void
    {
        $capability = new ToolsCapability();
        $this->assertNull($capability->getListChanged());
        $this->assertEquals([], $capability->toArray());
    }

    public function testCanBeInstantiatedWithTrueListChanged(): void
    {
        $capability = new ToolsCapability(true);
        $this->assertTrue($capability->getListChanged());
        $this->assertEquals(['listChanged' => true], $capability->toArray());
    }

    public function testCanBeInstantiatedWithFalseListChanged(): void
    {
        $capability = new ToolsCapability(false);
        $this->assertFalse($capability->getListChanged());
        $this->assertEquals(['listChanged' => false], $capability->toArray());
    }

    public function testCanBeCreatedFromEmptyArray(): void
    {
        $capability = ToolsCapability::create([]);
        $this->assertNull($capability->getListChanged());
        $this->assertEquals([], $capability->toArray());
    }

    public function testCanBeCreatedFromArrayWithListChanged(): void
    {
        $capability = ToolsCapability::create(['listChanged' => true]);
        $this->assertTrue($capability->getListChanged());
        $this->assertEquals(['listChanged' => true], $capability->toArray());
    }
}
