<?php

namespace Tests\Unit\Requests;

use LaravelMCP\MCP\Requests\CompletionRequest;
use Tests\TestCase;

class CompletionRequestTest extends TestCase
{
    public function testGetMethod(): void
    {
        $request = new CompletionRequest(
            argument: ['key' => 'value'],
            ref: ['refKey' => 'refValue']
        );

        $this->assertEquals('completion/complete', $request->getMethod());
    }

    public function testGetParams(): void
    {
        $argument = ['key' => 'value'];
        $ref = ['refKey' => 'refValue'];

        $request = new CompletionRequest(
            argument: $argument,
            ref: $ref
        );

        $params = $request->getParams();

        $this->assertEquals([
            'argument' => $argument,
            'ref' => $ref,
        ], $params);
    }

    public function testCreate(): void
    {
        $data = [
            'argument' => ['key' => 'value'],
            'ref' => ['refKey' => 'refValue'],
        ];

        $request = CompletionRequest::create($data);
        $params = $request->getParams();

        $this->assertEquals($data['argument'], $params['argument']);
        $this->assertEquals($data['ref'], $params['ref']);
    }

    public function testCreateWithEmptyArrays(): void
    {
        $data = [
            'argument' => [],
            'ref' => [],
        ];

        $request = CompletionRequest::create($data);
        $params = $request->getParams();

        $this->assertEmpty($params['argument']);
        $this->assertEmpty($params['ref']);
    }
}
