<?php

namespace LaravelMCP\MCP\Pagination;

class PaginatedResult
{
    /**
     * Create a new paginated result instance.
     */
    public function __construct(
        private ?string $nextCursor = null
    ) {
    }

    /**
     * Get the next cursor.
     */
    public function getNextCursor(): ?string
    {
        return $this->nextCursor;
    }

    /**
     * Convert the result to an array.
     */
    public function toArray(): array
    {
        if ($this->nextCursor === null) {
            return [];
        }

        return [
            'nextCursor' => $this->nextCursor,
        ];
    }

    /**
     * Create a new instance from an array.
     */
    public static function create(array $data): static
    {
        return new static(
            nextCursor: $data['nextCursor'] ?? null
        );
    }
}
