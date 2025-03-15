<?php

namespace Tests\Unit\Capabilities;

use LaravelMCP\MCP\Capabilities\RootsCapability;
use Tests\TestCase;

class RootsCapabilityTest extends TestCase
{
    public function testConstructor(): void
    {
        $capability = new RootsCapability(true, ['root1', 'root2']);
        $this->assertInstanceOf(RootsCapability::class, $capability);
    }

    public function testIsEnabled(): void
    {
        $capability = new RootsCapability(true, []);
        $this->assertTrue($capability->isEnabled());

        $capability = new RootsCapability(false, []);
        $this->assertFalse($capability->isEnabled());

        $capability = new RootsCapability();
        $this->assertFalse($capability->isEnabled());
    }

    public function testGetRoots(): void
    {
        $roots = ['root1', 'root2'];
        $capability = new RootsCapability(true, $roots);
        $this->assertEquals($roots, $capability->getRoots());

        $capability = new RootsCapability();
        $this->assertEquals([], $capability->getRoots());
    }

    public function testToArray(): void
    {
        $roots = ['root1', 'root2'];
        $capability = new RootsCapability(true, $roots);

        $expected = [
            'enabled' => true,
            'roots' => $roots,
        ];

        $this->assertEquals($expected, $capability->toArray());

        $capability = new RootsCapability();
        $expected = [
            'enabled' => false,
            'roots' => [],
        ];

        $this->assertEquals($expected, $capability->toArray());
    }

    public function testCreate(): void
    {
        $data = [
            'enabled' => true,
            'roots' => ['root1', 'root2'],
        ];

        $capability = RootsCapability::create($data);

        $this->assertTrue($capability->isEnabled());
        $this->assertEquals(['root1', 'root2'], $capability->getRoots());

        $capability = RootsCapability::create([]);

        $this->assertFalse($capability->isEnabled());
        $this->assertEquals([], $capability->getRoots());
    }
}
