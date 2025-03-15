# Prompt

Namespace: ``

Implementation of a prompt in the MCP system.

A prompt represents an interactive element that can be presented to users
or processed by the system. Each prompt has a unique name, a handler that
processes requests, and a set of messages that define its content.

Prompts can be used to:
- Gather user input
- Display system notifications
- Process structured messages
- Implement interactive workflows

@package LaravelMCP\MCP\Server

## Methods

### __construct

Create a new prompt instance.

@param string $name The unique identifier for the prompt
@param callable $handler The function that processes prompt requests
@param string|null $description Optional description of the prompt

### getName

{@inheritdoc}

### getMessages

{@inheritdoc}

### getDescription

{@inheritdoc}

### setDefaultArguments

Set the default arguments for the prompt.

@param array $arguments The default arguments

### getDefaultArguments

Get the default arguments for the prompt.

@return array The default arguments

### handle

Handle a prompt request.

@param array $arguments The arguments for the request
@return array The response data

### getArguments

{@inheritdoc}

### setMessages

Set the messages for this prompt.

@param array $messages The messages that make up the prompt's content

### getHandler

Get the handler function for this prompt.

@return callable The handler function

