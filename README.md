# Manus AI PHP/Laravel Client/SDK/Library

![Manus AI PHP Laravel SDK Client](https://i.postimg.cc/N09nSQy3/manus-ai-php-laravel-sdk-client.jpg)

[![Tests](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/tests.yml)
[![Coverage](https://codecov.io/gh/tigusigalpa/manus-ai-php/branch/main/graph/badge.svg)](https://app.codecov.io/gh/tigusigalpa/manus-ai-php)
[![CodeQL](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/codeql.yml)
[![Latest Stable Version](https://poser.pugx.org/tigusigalpa/manus-ai-php/v/stable)](https://packagist.org/packages/tigusigalpa/manus-ai-php)
[![PHP Version](https://img.shields.io/packagist/php-v/tigusigalpa/manus-ai-php)](https://packagist.org/packages/tigusigalpa/manus-ai-php)
[![License](https://poser.pugx.org/tigusigalpa/manus-ai-php/license)](https://github.com/tigusigalpa/manus-ai-php/blob/main/LICENSE)

An independent PHP 8.2+ client for [Manus API v2](https://open.manus.im/docs/v2/introduction), with optional Laravel integration. It creates and manages agent tasks, uploads attachments, receives webhook metadata, and exposes the v2 discovery, usage, and website endpoints.

[Русская документация](README-ru.md) · [API reference](https://open.manus.im/docs/v2/introduction) · [Go SDK](https://github.com/tigusigalpa/manus-ai-go)

See [CHANGELOG.md](CHANGELOG.md) for the API-v2 alignment work in this version.

> Manus controls API behaviour, available profiles, and permitted values. Check the official API reference whenever you upgrade an integration.

## Install

```bash
composer require tigusigalpa/manus-ai-php
```

The package requires PHP 8.2+, Guzzle 7, and Laravel 8–12 only when its Laravel integration is used.

## Quick start

Keep credentials out of source control:

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient($_ENV['MANUS_AI_API_KEY']);

$task = $client->createTask('Write a concise release note for this PHP package.', [
    'agent_profile' => 'standard',
    'title' => 'Release note',
]);

printf("Created %s: %s\n", $task['task_id'], $task['task_url']);
```

The task runs asynchronously. Poll it with `listMessages()` or use webhooks; `task.detail` only provides status and metadata.

## Authentication and client configuration

Use an API key for direct authentication:

```php
$client = new ManusAIClient('your-api-key');
```

For Manus Open Apps, pass an OAuth access token instead. The client sends either `Authorization: Bearer …` or `x-manus-api-key`, never both:

```php
$client = new ManusAIClient('', bearerToken: 'oauth-access-token');
```

The remaining constructor arguments support a custom base URL, Guzzle client, request timeout, connect timeout, and default task options:

```php
$client = new ManusAIClient(
    apiKey: $_ENV['MANUS_AI_API_KEY'],
    timeout: 45,
    defaultTaskOptions: ['locale' => 'en-US', 'share_visibility' => 'private'],
);
```

## Tasks

### Create a task

`createTask()` accepts API-native `snake_case` options. The legacy camelCase spellings are accepted where a compatible alias exists, but new code should use the current API names.

```php
use Tigusigalpa\ManusAI\Helpers\AgentProfile;
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

$task = $client->createTask('Summarize the attached report.', [
    'agent_profile' => AgentProfile::STANDARD, // standard, lite, or max
    'locale' => 'en-US',
    'title' => 'Report summary',
    'project_id' => 'project_123',
    'hide_in_task_list' => false,
    'share_visibility' => 'private', // private, team, or public
    'interactive_mode' => true,
    'structured_output_schema' => [
        'type' => 'object',
        'properties' => ['summary' => ['type' => 'string']],
        'required' => ['summary'],
        'additionalProperties' => false,
    ],
    'connectors' => ['connector_123'],
    'enable_skills' => ['skill_123'],
    'force_skills' => ['skill_456'],
    'task_references' => ['abcdefghijklmnopqrstuv'],
    'attachments' => [TaskAttachment::fromFileId('file_123')],
]);
```

`task_references` is limited by Manus to 20 bare 22-character task IDs. `enable_ask_user` / `enableAskUser` remains a compatibility alias for `interactive_mode`.

`AgentProfile::STANDARD`, `LITE`, and `MAX` are the current profile names. `MANUS_1_6`, `MANUS_1_6_LITE`, and `MANUS_1_6_MAX` are retained legacy aliases; Manus currently accepts them for compatibility.

### Inspect, list, update, delete

```php
$detail = $client->getTask($task['task_id']);
echo $detail['status'];

$tasks = $client->getTasks([
    'limit' => 20,
    'order' => 'desc',
    'scope' => 'all',
    'project_id' => 'project_123',
]);

$client->updateTask($task['task_id'], [
    'title' => 'Revised title',
    'enable_visible_in_task_list' => true,
    'share_visibility' => 'team',
]);

$client->deleteTask($task['task_id']);
```

The native v2 response for `task.detail` nests data under `task`. For convenience, `getTask()` preserves that `task` key and also promotes its fields (`id`, `status`, `agent_profile`, and so on) to the returned array's top level.

### Follow a conversation

```php
$messages = $client->listMessages($task['task_id'], limit: 50, order: 'desc', verbose: true);

$client->sendMessage(
    $task['task_id'],
    'Please make the summary more concise.',
    options: ['agent_profile' => 'lite'],
);

$client->stopTask($task['task_id']);

$client->confirmAction($task['task_id'], 'event_123', ['confirmed' => true]);
```

`sendMessage()` accepts the same attachment helpers and the message options `connectors`, `enable_skills`, `force_skills`, and `task_references`.

## Files and attachments

File upload has three steps: request a presigned URL, PUT the bytes, and attach the returned `file_id` to a task.

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

$file = $client->createFile('report.pdf');
$client->uploadFileContent(
    $file['upload_url'],
    file_get_contents('/path/to/report.pdf'),
    'application/pdf',
);

$attachment = TaskAttachment::fromFileId($file['file_id']);
$client->createTask('Review the attached report.', ['attachments' => [$attachment]]);
```

`createFile()` and `getFile()` retain the native `file` object while additionally exposing its properties at the top level; `file_id` is supplied as a convenience alias for `file.id`.

`TaskAttachment` provides `fromFileId()`, `fromUrl()`, `fromBase64()`, and `fromFilePath()`. `fromFilePath()` reads the whole file into memory, so use the presigned upload flow for large files.

There is **no** `/v2/file.list` endpoint. `listFiles()` is retained only as a deprecated method that throws a `ValidationException` before making a request. Store the ID returned by `createFile()` if it must be inspected with `getFile()` or removed with `deleteFile()`.

## Webhooks

```php
$webhook = $client->createWebhook([
    'url' => 'https://example.com/webhooks/manus',
]);

echo $webhook['webhook_id'];
```

The v2 create endpoint does not accept an `events` field; Manus registers lifecycle notifications automatically. Existing `events` values are ignored for compatibility.

```php
use Tigusigalpa\ManusAI\Helpers\WebhookHandler;

$payload = WebhookHandler::parsePayload($request->getContent());

if (WebhookHandler::isTaskCompleted($payload)) {
    $attachments = WebhookHandler::getAttachments($payload);
}
if (WebhookHandler::isTaskAskingForInput($payload)) {
    // Obtain the event ID from task.listMessages and call confirmAction().
}
```

Use `listWebhooks()`, `deleteWebhook()`, and `getWebhookPublicKey()` to manage registered webhooks and retrieve the key needed for signature verification.

## Additional API v2 resources

| Area | Methods |
| --- | --- |
| Projects and discovery | `createProject`, `listProjects`, `listSkills`, `listAgents`, `getAgent`, `updateAgent`, `listConnectors` |
| Browser and webhooks | `listOnlineBrowserClients`, `listWebhooks`, `getWebhookPublicKey` |
| Credit usage | `listUsage`, `getTeamUsageStatistic`, `listTeamUsageLog`, `getAvailableCredits` |
| Task websites | `getWebsiteStatus`, `listWebsiteCheckpoints`, `publishWebsite`, `updateWebsite` |

Every method returns the decoded v2 JSON response. See the matching [official API endpoint documentation](https://open.manus.im/docs/v2/introduction) for accepted option values and response fields.

## Laravel

Laravel discovers the provider automatically. Publish the configuration if you need to change defaults:

```bash
php artisan vendor:publish --tag=manus-ai-config
```

```env
MANUS_AI_API_KEY=your-api-key
# Or, instead of an API key:
# MANUS_AI_BEARER_TOKEN=oauth-access-token
MANUS_AI_DEFAULT_AGENT_PROFILE=standard
MANUS_AI_DEFAULT_LOCALE=en-US
MANUS_AI_SHARE_VISIBILITY=private
MANUS_AI_INTERACTIVE_MODE=false
```

The provider registers `ManusAIClientInterface`, `ManusAIClient`, and the `ManusAI` facade against the same singleton. Request and connect timeouts from `config/manus-ai.php` are used when that client is created.

```php
use Tigusigalpa\ManusAI\Contracts\ManusAIClientInterface;
use Tigusigalpa\ManusAI\Laravel\ManusAI;

// Dependency injection
function createReleaseNote(ManusAIClientInterface $manus): array
{
    return $manus->createTask('Write a release note.');
}

// Or the facade
$task = ManusAI::createTask('Write a release note.');
```

Available Artisan commands:

```bash
php artisan manus-ai:test
php artisan manus-ai:task create --prompt="Write a release note" --profile=standard
php artisan manus-ai:task list --limit=10
php artisan manus-ai:task get --id=task_123
php artisan manus-ai:task update --id=task_123 --title="Revised title"
php artisan manus-ai:task delete --id=task_123
```

## Errors

- `AuthenticationException` for invalid credentials or HTTP 401/403 responses.
- `ValidationException` for invalid client input and API `invalid_argument` responses.
- `ManusAIException` for other transport, HTTP, or response-decoding failures.

API failures include the HTTP status, Manus error code, and `request_id` (when supplied) in `getContext()`. Never log API keys or bearer tokens.

## Testing

```bash
composer install
composer test
```

The test suite uses mocked HTTP responses and does not make calls to Manus.

## Migrating from the older PHP client

- Replace removed `taskMode` with the current task fields such as `interactive_mode`, `project_id`, and `share_visibility`.
- Replace `enable_ask_user` with `interactive_mode`; the old name remains accepted as an alias.
- Replace task-update `hide_in_task_list` with `enable_visible_in_task_list`. The old alias is inverted automatically for compatibility.
- Read task status from `getTask($id)['status']` and event history/output from `listMessages()`.
- Use `file_id` from `createFile()` and remove any `listFiles()` calls.
- Prefer `standard`, `lite`, or `max` for new `agent_profile` requests.

## License

MIT. See [LICENSE](LICENSE).
