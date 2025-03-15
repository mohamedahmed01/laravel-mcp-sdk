<?php

namespace LaravelMCP\MCP\Tests\Unit\Pagination;

use LaravelMCP\MCP\Pagination\PaginatedRequest;
use LaravelMCP\MCP\Pagination\PaginatedResult;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testPaginatedRequest()
    {
        $request = new PaginatedRequest('next-page-token');

        $this->assertEquals('next-page-token', $request->getCursor());
        $this->assertEquals([
            'cursor' => 'next-page-token',
        ], $request->getParams());
    }

    public function testPaginatedRequestWithoutCursor()
    {
        $request = new PaginatedRequest();

        $this->assertNull($request->getCursor());
        $this->assertEquals([], $request->getParams());
    }

    public function testPaginatedRequestCreatesFromArray()
    {
        $request = PaginatedRequest::create([
            'cursor' => 'next-page-token',
        ]);

        $this->assertEquals('next-page-token', $request->getCursor());
        $this->assertEquals([
            'cursor' => 'next-page-token',
        ], $request->getParams());
    }

    public function testPaginatedRequestCreatesFromArrayWithoutCursor()
    {
        $request = PaginatedRequest::create([]);

        $this->assertNull($request->getCursor());
        $this->assertEquals([], $request->getParams());
    }

    public function testPaginatedResult()
    {
        $result = new PaginatedResult('next-page-token');

        $this->assertEquals('next-page-token', $result->getNextCursor());
        $this->assertEquals([
            'nextCursor' => 'next-page-token',
        ], $result->toArray());
    }

    public function testPaginatedResultWithoutCursor()
    {
        $result = new PaginatedResult();

        $this->assertNull($result->getNextCursor());
        $this->assertEquals([], $result->toArray());
    }

    public function testPaginatedResultCreatesFromArray()
    {
        $result = PaginatedResult::create([
            'nextCursor' => 'next-page-token',
        ]);

        $this->assertEquals('next-page-token', $result->getNextCursor());
        $this->assertEquals([
            'nextCursor' => 'next-page-token',
        ], $result->toArray());
    }

    public function testPaginatedResultCreatesFromArrayWithoutCursor()
    {
        $result = PaginatedResult::create([]);

        $this->assertNull($result->getNextCursor());
        $this->assertEquals([], $result->toArray());
    }

    public function testPaginatedRequestMethod(): void
    {
        $request = new PaginatedRequest();
        $this->assertEquals('pagination/request', $request->getMethod());
    }
}
