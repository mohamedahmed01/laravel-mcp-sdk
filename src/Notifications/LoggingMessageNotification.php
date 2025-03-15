<?php

namespace LaravelMCP\MCP\Notifications;

use LaravelMCP\MCP\Contracts\NotificationInterface;

class LoggingMessageNotification implements NotificationInterface
{
    public function __construct(
        private mixed $data,
        private string $level,
        private ?string $logger = null
    ) {
    }

    public function getMethod(): string
    {
        return 'notifications/message';
    }

    public function getParams(): array
    {
        return [
            'type' => 'log',
            'message' => $this->data,
            'level' => $this->level,
            'logger' => $this->logger,
        ];
    }

    /**
     * Get the type of the notification.
     *
     * @return string The notification type identifier
     */
    public function getType(): string
    {
        return 'log';
    }

    /**
     * Get the data associated with the notification.
     *
     * @return array The notification data
     */
    public function getData(): array
    {
        return [
            'message' => $this->data,
            'level' => $this->level,
            'logger' => $this->logger,
        ];
    }
}
