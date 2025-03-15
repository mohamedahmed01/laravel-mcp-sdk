Laravel MCP SDK Documentation
==========================

Welcome to the Laravel MCP SDK documentation. This documentation is generated from the PHPDoc comments in the source code.

Installation
-----------

You can install the package via composer:

.. code-block:: bash

    composer require laravelmcp/mcp

Basic Usage
----------

First, register the service provider in your ``config/app.php`` file:

.. code-block:: php

    'providers' => [
        // ...
        LaravelMCP\MCP\MCPServiceProvider::class,
    ],

Then, you can use the MCP facade:

.. code-block:: php

    use LaravelMCP\MCP\Facades\MCP;

    // Create a new MCP client
    $client = MCP::client();

    // Send a request
    $response = $client->sendRequest($request);

Components
---------

The SDK consists of several main components:

1. **Transport Layer**
   - HTTP Transport
   - WebSocket Transport
   - Standard I/O Transport

2. **Server Components**
   - MCP Server
   - Tools
   - Resources
   - Prompts

3. **Capabilities**
   - Server Capabilities
   - Client Capabilities
   - Resource Capabilities
   - Tool Capabilities

For detailed API documentation, please refer to the generated API documentation. 