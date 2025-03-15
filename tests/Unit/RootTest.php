<?php

namespace LaravelMCP\MCP\Tests\Unit;

use LaravelMCP\MCP\Root;
use PHPUnit\Framework\TestCase;

class RootTest extends TestCase
{
    public function testItCreatesRoot()
    {
        $root = new Root('file:///path/to/root', 'My Root');

        $this->assertEquals('file:///path/to/root', $root->getUri());
        $this->assertEquals('My Root', $root->getName());
        $this->assertEquals([
            'uri' => 'file:///path/to/root',
            'name' => 'My Root',
        ], $root->toArray());
    }

    public function testItCreatesRootWithoutName()
    {
        $root = new Root('file:///path/to/root');

        $this->assertEquals('file:///path/to/root', $root->getUri());
        $this->assertNull($root->getName());
        $this->assertEquals([
            'uri' => 'file:///path/to/root',
        ], $root->toArray());
    }

    public function testItCreatesFromArray()
    {
        $root = Root::create([
            'uri' => 'file:///path/to/root',
            'name' => 'My Root',
        ]);

        $this->assertEquals('file:///path/to/root', $root->getUri());
        $this->assertEquals('My Root', $root->getName());
        $this->assertEquals([
            'uri' => 'file:///path/to/root',
            'name' => 'My Root',
        ], $root->toArray());
    }

    public function testItCreatesFromArrayWithoutName()
    {
        $root = Root::create([
            'uri' => 'file:///path/to/root',
        ]);

        $this->assertEquals('file:///path/to/root', $root->getUri());
        $this->assertNull($root->getName());
        $this->assertEquals([
            'uri' => 'file:///path/to/root',
        ], $root->toArray());
    }

    public function testItThrowsExceptionForInvalidUri()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Root URI must start with file://');

        new Root('invalid://uri');
    }

    public function testItReturnsPath()
    {
        $root = new Root('file:///path/to/root');
        $this->assertEquals('/path/to/root', $root->getPath());

        $root = new Root('file://C:/path/to/root');
        $this->assertEquals('C:/path/to/root', $root->getPath());
    }
}
