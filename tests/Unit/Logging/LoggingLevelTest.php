<?php

namespace LaravelMCP\MCP\Tests\Unit\Logging;

use LaravelMCP\MCP\Logging\LoggingLevel;
use PHPUnit\Framework\TestCase;

class LoggingLevelTest extends TestCase
{
    public function testItCreatesLoggingLevels()
    {
        $this->assertEquals('debug', LoggingLevel::DEBUG->value);
        $this->assertEquals('info', LoggingLevel::INFO->value);
        $this->assertEquals('notice', LoggingLevel::NOTICE->value);
        $this->assertEquals('warning', LoggingLevel::WARNING->value);
        $this->assertEquals('error', LoggingLevel::ERROR->value);
        $this->assertEquals('critical', LoggingLevel::CRITICAL->value);
        $this->assertEquals('alert', LoggingLevel::ALERT->value);
        $this->assertEquals('emergency', LoggingLevel::EMERGENCY->value);
    }

    public function testItCreatesFromString()
    {
        $this->assertEquals(LoggingLevel::DEBUG, LoggingLevel::fromString('debug'));
        $this->assertEquals(LoggingLevel::INFO, LoggingLevel::fromString('info'));
        $this->assertEquals(LoggingLevel::NOTICE, LoggingLevel::fromString('notice'));
        $this->assertEquals(LoggingLevel::WARNING, LoggingLevel::fromString('warning'));
        $this->assertEquals(LoggingLevel::ERROR, LoggingLevel::fromString('error'));
        $this->assertEquals(LoggingLevel::CRITICAL, LoggingLevel::fromString('critical'));
        $this->assertEquals(LoggingLevel::ALERT, LoggingLevel::fromString('alert'));
        $this->assertEquals(LoggingLevel::EMERGENCY, LoggingLevel::fromString('emergency'));
    }

    public function testItThrowsExceptionForInvalidLevel()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid logging level: invalid');

        LoggingLevel::fromString('invalid');
    }
}
