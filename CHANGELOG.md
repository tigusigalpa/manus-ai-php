# Changelog

All notable changes to this package are documented here. This project follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Fixed

- Decode current `task.detail`, `file.upload`, `file.detail`, and `webhook.create` response envelopes while retaining both the native nested object and convenient top-level fields.
- Send `interactive_mode` for new tasks and `enable_visible_in_task_list` for task updates; retain compatible aliases for existing integrations.
- Accept every successful HTTP upload response (the full 2xx range), not only 200.
- Return structured Manus errors with HTTP status, API error code, and request ID in the exception context.
- Prevent calls to the non-existent API v2 `file.list` endpoint; `listFiles()` now fails locally with a clear deprecation error.

### Added

- OAuth bearer-token authentication for Manus Open Apps.
- `task_references`, `structured_output_schema`, and message options for follow-up messages.
- Projects, skills, agents, connectors, webhooks, browser clients, usage, credit, and task-website API v2 methods, matching the maintained Go SDK's public coverage.
- Laravel bindings for the client contract, configurable default task options, credentials, and timeouts.

### Changed

- Default profile names and documentation now use `standard`, `lite`, and `max`; legacy versioned aliases remain available.
- English and Russian documentation, examples, Artisan help, and tests now match Manus API v2.
