# Changelog

All notable changes to `manus-ai-php` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-12

### Added
- Initial release of Manus AI PHP SDK
- Full support for Manus AI API endpoints
- Task management (create, get, list, update, delete)
- File management with upload support
- Webhook integration
- Laravel service provider and facade
- Artisan commands for CLI usage
- Helper classes for attachments and webhooks
- Comprehensive PHPUnit tests
- Complete documentation with examples
- Support for PHP 8.2+
- Support for Laravel 8, 9, 10, 11, and 12

### Features
- `ManusAIClient` - Main client for API interactions
- `TaskAttachment` helper for handling file attachments
- `WebhookHandler` helper for processing webhook events
- Laravel Facade for easy integration
- Artisan commands: `manus-ai:test`, `manus-ai:task`
- Exception handling with custom exceptions
- Type-safe interfaces and contracts

## [Unreleased]

### Planned
- Support for connectors
- Batch task operations
- Enhanced error handling and retry logic
- Event listeners for Laravel
- Queue integration for async operations
- More helper utilities
