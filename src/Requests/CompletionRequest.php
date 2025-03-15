<?php

namespace LaravelMCP\MCP\Requests;

use LaravelMCP\MCP\Contracts\RequestInterface;

class CompletionRequest implements RequestInterface
{
    public function __construct(
        private array $argument,
        private array $ref
    ) {
    }

    public function getType(): string
    {
        return 'completion';
    }

    public function getArguments(): array
    {
        return [
            'argument' => $this->argument,
            'ref' => $this->ref,
        ];
    }

    public function getMethod(): string
    {
        return 'completion/complete';
    }

    public function getParams(): array
    {
        return [
            'argument' => $this->argument,
            'ref' => $this->ref,
        ];
    }

    public static function create(array $data): static
    {
        return new static(
            argument: $data['argument'],
            ref: $data['ref']
        );
    }
}
