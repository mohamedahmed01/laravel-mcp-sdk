# Laravel MCP File Index

## Root Directory
- `README.md` - Main documentation and usage guide
- `composer.json` - Package dependencies and metadata
- `composer.lock` - Locked package versions
- `phpunit.xml` - PHPUnit test configuration
- `phpstan.neon` - PHPStan static analysis configuration
- `phpcs.xml` - PHP CodeSniffer configuration
- `.php-cs-fixer.php` - PHP CS Fixer configuration
- `.env.example` - Example environment configuration
- `CONTRIBUTING.md` - Contribution guidelines
- `LICENSE.md` - MIT license details
- `check.bat` - Windows batch script for running checks

## Source Code (`src/`)
### Core Files
- `MCPServiceProvider.php` - Laravel service provider for MCP
- `MCPClient.php` - Main client implementation
- `Root.php` - Root management implementation
- `Implementation.php` - Base implementation class

### Directories
- `Contracts/` - Interface definitions
- `Capabilities/` - Feature capability implementations
- `Logging/` - Logging system implementation
- `Pagination/` - Pagination support classes
- `Requests/` - Request handling classes
- `Notifications/` - Progress notification system
- `Sampling/` - Sampling functionality
- `Transport/` - Transport layer implementations (HTTP, WebSocket, Stdio)
- `Commands/` - Artisan command implementations
- `Server/` - Server-side implementations
- `Facades/` - Laravel facade implementations

## Examples (`examples/`)
- `README.md` - Examples documentation
- `http_server.php` - HTTP transport server example
- `http_client.php` - HTTP transport client example
- `websocket_server.php` - WebSocket transport server example
- `websocket_client.php` - WebSocket transport client example
- `cli_tool.php` - Command-line interface example

## Tests (`tests/`)
### Core Test Files
- `TestCase.php` - Base test case class

### Test Directories
- `Unit/` - Unit tests
- `Feature/` - Feature tests
- `Transport/` - Transport layer tests
- `Commands/` - Command tests
- `Facades/` - Facade tests
- `Server/` - Server implementation tests

## Configuration (`config/`)
- Configuration files for the package

## GitHub Workflows (`.github/`)
- GitHub Actions workflow configurations

## Build and Cache Directories
- `build/` - Build artifacts
- `vendor/` - Composer dependencies
- `.phpunit.cache/` - PHPUnit cache
- `.git/` - Git repository data

## Development Configuration Files
- `.gitignore` - Git ignore rules
- `.php-cs-fixer.cache` - PHP CS Fixer cache
- `phpunit.xml.bak` - PHPUnit configuration backup
- `.phpunit.result.cache` - PHPUnit results cache

## Directory Structure
```
laravelmcp/
├── src/                    # Source code
│   ├── Contracts/         # Interfaces
│   ├── Capabilities/      # Feature implementations
│   ├── Transport/         # Transport implementations
│   └── ...               # Other components
├── tests/                 # Test suite
│   ├── Unit/             # Unit tests
│   ├── Feature/          # Feature tests
│   └── ...              # Other test categories
├── examples/             # Example implementations
├── config/              # Configuration files
└── .github/             # GitHub configurations
```

## Key Components
1. **Core Implementation**
   - `MCPClient.php` - Main client class
   - `MCPServiceProvider.php` - Service provider
   - `Root.php` - Root management

2. **Transport Layer**
   - HTTP implementation
   - WebSocket implementation
   - Stdio implementation

3. **Feature Modules**
   - Capabilities system
   - Logging system
   - Pagination support
   - Notification system
   - Resource management

4. **Development Tools**
   - PHPUnit for testing
   - PHPStan for static analysis
   - PHP CS Fixer for code style
   - Composer for dependency management 