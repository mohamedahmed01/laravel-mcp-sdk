<?php

namespace LaravelMCP\MCP\Tests\Unit;

use LaravelMCP\MCP\Implementation;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    public function testItCreatesImplementation()
    {
        $implementation = new Implementation('Test App', '1.0.0');

        $this->assertEquals('Test App', $implementation->getName());
        $this->assertEquals('1.0.0', $implementation->getVersion());
        $this->assertEquals([
            'name' => 'Test App',
            'version' => '1.0.0',
        ], $implementation->toArray());
    }

    public function testItCreatesFromArray()
    {
        $implementation = Implementation::create([
            'name' => 'Test App',
            'version' => '1.0.0',
        ]);

        $this->assertEquals('Test App', $implementation->getName());
        $this->assertEquals('1.0.0', $implementation->getVersion());
        $this->assertEquals([
            'name' => 'Test App',
            'version' => '1.0.0',
        ], $implementation->toArray());
    }

    public function testItThrowsExceptionWhenNameIsMissing()
    {
        $this->expectException(\TypeError::class);
        // Don't check for specific error message as it may vary between PHP versions
        Implementation::create([
            'version' => '1.0.0',
        ]);
    }

    public function testItThrowsExceptionWhenVersionIsMissing()
    {
        $this->expectException(\TypeError::class);
        // Don't check for specific error message as it may vary between PHP versions
        Implementation::create([
            'name' => 'Test App',
        ]);
    }
}
