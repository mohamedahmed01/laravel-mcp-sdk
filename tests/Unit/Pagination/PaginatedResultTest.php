<?php

namespace Tests\Unit\Pagination;

use LaravelMCP\MCP\Pagination\PaginatedResult;
use Tests\TestCase;

class PaginatedResultTest extends TestCase
{
    public function testConstructorAndGetNextCursor(): void
    {
        $cursor = 'next_page_token';
        $result = new PaginatedResult($cursor);

        $this->assertEquals($cursor, $result->getNextCursor());
    }

    public function testConstructorWithNullCursor(): void
    {
        $result = new PaginatedResult();

        $this->assertNull($result->getNextCursor());
    }

    public function testToArrayWithCursor(): void
    {
        $cursor = 'next_page_token';
        $result = new PaginatedResult($cursor);

        $array = $result->toArray();

        $this->assertEquals([
            'nextCursor' => $cursor,
        ], $array);
    }

    public function testToArrayWithNullCursor(): void
    {
        $result = new PaginatedResult();

        $array = $result->toArray();

        $this->assertEmpty($array);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'nextCursor' => 'next_page_token',
        ];

        $result = PaginatedResult::create($data);

        $this->assertEquals($data['nextCursor'], $result->getNextCursor());
        $this->assertEquals($data, $result->toArray());
    }

    public function testCreateFromEmptyArray(): void
    {
        $result = PaginatedResult::create([]);

        $this->assertNull($result->getNextCursor());
        $this->assertEmpty($result->toArray());
    }

    public function testCreateFromArrayWithNullCursor(): void
    {
        $data = [
            'nextCursor' => null,
        ];

        $result = PaginatedResult::create($data);

        $this->assertNull($result->getNextCursor());
        $this->assertEmpty($result->toArray());
    }
}
