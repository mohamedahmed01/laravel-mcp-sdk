<?php

namespace LaravelMCP\MCP\Notifications;

use LaravelMCP\MCP\Contracts\NotificationInterface;

class ProgressNotification implements NotificationInterface
{
    public function __construct(
        private float $progress,
        private string|int $progressToken,
        private ?float $total = null
    ) {
    }

    public function getType(): string
    {
        return 'progress';
    }

    public function getData(): array
    {
        return [
            'progress' => $this->progress,
            'progressToken' => $this->progressToken,
            'total' => $this->total,
        ];
    }

    public function getMethod(): string
    {
        return 'notifications/progress';
    }

    public function getParams(): array
    {
        $params = [
            'progress' => $this->progress,
            'progressToken' => $this->progressToken,
        ];

        if ($this->total !== null) {
            $params['total'] = $this->total;
        }

        return $params;
    }

    public static function create(array $data): self
    {
        return new self(
            progress: $data['progress'],
            progressToken: $data['progressToken'],
            total: $data['total'] ?? null
        );
    }
}
