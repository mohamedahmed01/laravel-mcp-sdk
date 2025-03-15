<?php

namespace LaravelMCP\MCP\Tests\Unit\Notifications;

use LaravelMCP\MCP\Notifications\ProgressNotification;
use PHPUnit\Framework\TestCase;

class ProgressNotificationTest extends TestCase
{
    public function testItCreatesProgressNotification()
    {
        $notification = new ProgressNotification(0.5, 'token-123', 1.0);

        $this->assertEquals('notifications/progress', $notification->getMethod());
        $this->assertEquals([
            'progress' => 0.5,
            'progressToken' => 'token-123',
            'total' => 1.0,
        ], $notification->getParams());
    }

    public function testItCreatesProgressNotificationWithoutTotal()
    {
        $notification = new ProgressNotification(0.5, 'token-123');

        $this->assertEquals('notifications/progress', $notification->getMethod());
        $this->assertEquals([
            'progress' => 0.5,
            'progressToken' => 'token-123',
        ], $notification->getParams());
    }

    public function testItCreatesFromArray()
    {
        $notification = ProgressNotification::create([
            'progress' => 0.5,
            'progressToken' => 'token-123',
            'total' => 1.0,
        ]);

        $this->assertEquals('notifications/progress', $notification->getMethod());
        $this->assertEquals([
            'progress' => 0.5,
            'progressToken' => 'token-123',
            'total' => 1.0,
        ], $notification->getParams());
    }

    public function testItCreatesFromArrayWithoutTotal()
    {
        $notification = ProgressNotification::create([
            'progress' => 0.5,
            'progressToken' => 'token-123',
        ]);

        $this->assertEquals('notifications/progress', $notification->getMethod());
        $this->assertEquals([
            'progress' => 0.5,
            'progressToken' => 'token-123',
        ], $notification->getParams());
    }
}
