<?php

namespace LaravelMCP\MCP\Pagination;

use LaravelMCP\MCP\Contracts\RequestInterface;

class PaginatedRequest implements RequestInterface
{
    /**
     * Create a new paginated request instance.
     */
    public function __construct(
        private ?string $cursor = null
    ) {
    }

    /**
     * Get the type of the request.
     */
    public function getType(): string
    {
        return 'pagination';
    }

    /**
     * Get the arguments associated with the request.
     */
    public function getArguments(): array
    {
        return [
            'cursor' => $this->cursor,
        ];
    }

    /**
     * Get the cursor.
     */
    public function getCursor(): ?string
    {
        return $this->cursor;
    }

    /**
     * Get the request method name.
     */
    public function getMethod(): string
    {
        return 'pagination/request';
    }

    /**
     * Get the request parameters.
     */
    public function getParams(): array
    {
        if ($this->cursor === null) {
            return [];
        }

        return [
            'cursor' => $this->cursor,
        ];
    }

    /**
     * Create a new instance from an array.
     */
    public static function create(array $data): static
    {
        return new static(
            cursor: $data['cursor'] ?? null
        );
    }
}
