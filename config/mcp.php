<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your MCP server settings.
    |
    */

    'server_name' => env('MCP_SERVER_NAME', 'Laravel MCP'),
    'server_version' => env('MCP_SERVER_VERSION', '1.0.0'),
    'base_url' => env('MCP_BASE_URL', 'https://api.mcp.example.com'),
    'api_key' => env('MCP_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Server Capabilities
    |--------------------------------------------------------------------------
    |
    | Configure which MCP capabilities your server supports.
    |
    */

    'capabilities' => [
        'prompts' => [
            'listChanged' => true,
        ],
        'resources' => [
            'subscribe' => true,
            'listChanged' => true,
        ],
        'tools' => [
            'listChanged' => true,
        ],
        'logging' => true,
        'completion' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Here you can set default values for various MCP operations.
    |
    */

    'defaults' => [
        'timeout' => env('MCP_TIMEOUT', 30),
        'retry_attempts' => env('MCP_RETRY_ATTEMPTS', 3),
        'max_connections' => env('MCP_MAX_CONNECTIONS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transport Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the MCP server communicates with clients.
    |
    */

    'transport' => [
        'type' => env('MCP_TRANSPORT_TYPE', 'http'), // http, websocket, stdio
        'host' => env('MCP_HOST', '127.0.0.1'),
        'port' => env('MCP_PORT', 3000),
    ],
]; 