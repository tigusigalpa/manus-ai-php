# Manus AI PHP SDK

<div align="center">
  <img src="https://github.com/user-attachments/assets/eb30d0e5-3d22-4edb-bb42-dd4e751b5cf4" alt="Monica AI PHP SDK" style="max-width: 100%; height: auto;">
</div>

🚀 Complete PHP library for integration with [Manus AI](https://manus.ai) API. Easily integrate Manus AI agent into your PHP applications with full Laravel support.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-8%7C9%7C10%7C11%7C12-orange)](https://laravel.com/)

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Basic Usage](#basic-usage)
  - [Task Management](#task-management)
  - [File Management](#file-management)
  - [Webhooks](#webhooks)
  - [Laravel Integration](#laravel-integration)
- [API Reference](#api-reference)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

- ✅ Full support for Manus AI API
- ✅ Task creation and management
- ✅ File upload and attachment handling
- ✅ Webhook integration for real-time updates
- ✅ Laravel service provider and facade
- ✅ Artisan commands for CLI usage
- ✅ Comprehensive error handling
- ✅ Type-safe interfaces
- ✅ PHPUnit tests included
- ✅ Detailed documentation and examples

## 📦 Requirements

- PHP 8.2 or higher
- Composer
- Guzzle HTTP client (^7.8)
- Laravel 8+ (optional, for Laravel integration)

## 🔧 Installation

Install the package via Composer:

```bash
composer require tigusigalpa/manus-ai-php
```

## ⚙️ Configuration

### Getting Your API Key

1. Sign up at [Manus AI](https://manus.im)
2. Get your API key from the [API Integration settings](http://manus.im/app?show_settings=integrations&app_name=api)

### PHP Configuration

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$apiKey = 'your-api-key-here';
$client = new ManusAIClient($apiKey);
```

### Laravel Configuration

#### 1. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=manus-ai-config
```

#### 2. Add to `.env`

```env
MANUS_AI_API_KEY=your-api-key-here
MANUS_AI_DEFAULT_AGENT_PROFILE=manus-1.6
MANUS_AI_DEFAULT_TASK_MODE=agent
```

## 🚀 Usage

This SDK provides a simple and intuitive interface to interact with Manus AI's powerful automation capabilities. Whether you're creating AI-powered tasks, managing file attachments, or setting up real-time notifications, the SDK handles all the complexity of API communication for you.

### Basic Usage

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient('your-api-key');

// Create a task
$result = $client->createTask('Write a poem about PHP programming', [
    'agentProfile' => 'manus-1.6',
    'taskMode' => 'chat',
]);

echo "Task created: {$result['task_id']}\n";
echo "View at: {$result['task_url']}\n";
```

### Task Management

Tasks are the core of Manus AI - they represent AI agent work items that can perform complex operations, answer questions, or automate workflows. Each task has a lifecycle from creation through execution to completion, and you can monitor and control every step of the process.

**API Documentation:** [Tasks API Reference](https://open.manus.ai/docs/api-reference/create-task)

#### Create a Task

[API Docs: Create Task](https://open.manus.ai/docs/api-reference/create-task)

```php
use Tigusigalpa\ManusAI\Helpers\AgentProfile;

$task = $client->createTask('Your task prompt here', [
    'agentProfile' => AgentProfile::MANUS_1_6,  // or AgentProfile::MANUS_1_6_LITE, MANUS_1_6_MAX
    'taskMode' => 'agent',                       // 'chat', 'adaptive', or 'agent'
    'locale' => 'en-US',
    'hideInTaskList' => false,
    'createShareableLink' => true,
]);
```

**Available Agent Profiles:**
- `AgentProfile::MANUS_1_6` - Latest and most capable model (recommended)
- `AgentProfile::MANUS_1_6_LITE` - Faster, lightweight version
- `AgentProfile::MANUS_1_6_MAX` - Maximum capability version
- `AgentProfile::SPEED` - ⚠️ Deprecated, use `MANUS_1_6_LITE` instead
- `AgentProfile::QUALITY` - ⚠️ Deprecated, use `MANUS_1_6` instead

```php
// Or use string values directly
$task = $client->createTask('Your prompt', [
    'agentProfile' => 'manus-1.6',
]);
```

#### Get Task Details

[API Docs: Get Task](https://open.manus.ai/docs/api-reference/get-task)

```php
$task = $client->getTask('task_id');

echo "Status: {$task['status']}\n";
echo "Credits used: {$task['credit_usage']}\n";

// Access output messages
foreach ($task['output'] as $message) {
    echo "[{$message['role']}]: {$message['content']}\n";
}
```

#### List Tasks

[API Docs: Get Tasks](https://open.manus.ai/docs/api-reference/get-tasks)

```php
$tasks = $client->getTasks([
    'limit' => 10,
    'order' => 'desc',
    'orderBy' => 'created_at',
    'status' => ['completed', 'running'],
]);

foreach ($tasks['data'] as $task) {
    echo "Task {$task['id']}: {$task['status']}\n";
}
```

#### Update Task

[API Docs: Update Task](https://open.manus.ai/docs/api-reference/update-task)

```php
$updated = $client->updateTask('task_id', [
    'title' => 'New Task Title',
    'enableShared' => true,
    'enableVisibleInTaskList' => true,
]);
```

#### Delete Task

[API Docs: Delete Task](https://open.manus.ai/docs/api-reference/delete-task)

```php
$result = $client->deleteTask('task_id');
echo "Deleted: " . ($result['deleted'] ? 'yes' : 'no') . "\n";
```

### File Management

Manus AI supports file attachments to provide context for your tasks. The file upload process uses a two-step approach: first, create a file record to get a secure presigned URL, then upload your content directly to cloud storage. Once uploaded, files can be attached to tasks for analysis, processing, or reference.

**API Documentation:** [Files API Reference](https://open.manus.ai/docs/api-reference/create-file)

#### Upload a File

[API Docs: Create File](https://open.manus.ai/docs/api-reference/create-file)

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// 1. Create file record
$fileResult = $client->createFile('document.pdf');

// 2. Upload file content
$fileContent = file_get_contents('/path/to/document.pdf');
$client->uploadFileContent(
    $fileResult['upload_url'],
    $fileContent,
    'application/pdf'
);

// 3. Use file in task
$attachment = TaskAttachment::fromFileId($fileResult['id']);

$task = $client->createTask('Analyze this document', [
    'attachments' => [$attachment],
]);
```

#### Different Attachment Types

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// From file ID
$attachment1 = TaskAttachment::fromFileId('file_123');

// From URL
$attachment2 = TaskAttachment::fromUrl('https://example.com/image.jpg');

// From base64
$attachment3 = TaskAttachment::fromBase64($base64Data, 'image/png');

// From local file path
$attachment4 = TaskAttachment::fromFilePath('/path/to/file.pdf');
```

#### List Files

[API Docs: List Files](https://open.manus.ai/docs/api-reference/list-files)

```php
$files = $client->listFiles();

foreach ($files['data'] as $file) {
    echo "{$file['filename']} - {$file['status']}\n";
}
```

#### Delete File

[API Docs: Delete File](https://open.manus.ai/docs/api-reference/delete-file)

```php
$result = $client->deleteFile('file_id');
```

### Webhooks

Webhooks enable real-time notifications about your task lifecycle events. Instead of polling for updates, Manus AI will send HTTP POST requests to your specified endpoint whenever important events occur - such as task creation or completion. This allows you to build reactive workflows, update dashboards instantly, or trigger automated actions based on task results.

Manus AI supports two key event types:
- **task_created**: Fired immediately when a task is created via the API
- **task_stopped**: Fired when a task completes successfully or requires user input

**API Documentation:** [Webhooks Guide](https://open.manus.ai/docs/webhooks/index)

#### Create Webhook

[API Docs: Create Webhook](https://open.manus.ai/docs/api-reference/create-webhook)

```php
$webhook = $client->createWebhook([
    'url' => 'https://your-domain.com/webhook/manus-ai',
    'events' => ['task_created', 'task_stopped'],
]);

echo "Webhook ID: {$webhook['webhook_id']}\n";
```

#### Handle Webhook Events

```php
use Tigusigalpa\ManusAI\Helpers\WebhookHandler;

// In your webhook endpoint
$payload = WebhookHandler::parsePayload($request->getContent());

if (WebhookHandler::isTaskCompleted($payload)) {
    $taskDetail = WebhookHandler::getTaskDetail($payload);
    $attachments = WebhookHandler::getAttachments($payload);
    
    echo "Task completed: {$taskDetail['task_id']}\n";
    echo "Message: {$taskDetail['message']}\n";
    
    // Download attachments
    foreach ($attachments as $attachment) {
        echo "File: {$attachment['file_name']} ({$attachment['size_bytes']} bytes)\n";
        echo "URL: {$attachment['url']}\n";
    }
}

if (WebhookHandler::isTaskAskingForInput($payload)) {
    // Task needs user input
    $taskDetail = WebhookHandler::getTaskDetail($payload);
    echo "Input required: {$taskDetail['message']}\n";
}
```

#### Delete Webhook

[API Docs: Delete Webhook](https://open.manus.ai/docs/api-reference/delete-webhook)

```php
$client->deleteWebhook('webhook_id');
```

### Laravel Integration

The SDK includes first-class Laravel support with a service provider, facade, and Artisan commands. Once configured, you can use Manus AI seamlessly within your Laravel application through dependency injection or the convenient facade.

#### Using Facade

```php
use Tigusigalpa\ManusAI\Laravel\ManusAI;

// Create task
$result = ManusAI::createTask('Your prompt here');

// Get task
$task = ManusAI::getTask('task_id');

// List tasks
$tasks = ManusAI::getTasks(['limit' => 10]);
```

#### Using Dependency Injection

```php
use Tigusigalpa\ManusAI\ManusAIClient;

class TaskController extends Controller
{
    public function create(ManusAIClient $manus)
    {
        $result = $manus->createTask('Generate report');
        
        return response()->json([
            'task_id' => $result['task_id'],
            'url' => $result['task_url'],
        ]);
    }
}
```

#### Artisan Commands

Test connection:
```bash
php artisan manus-ai:test
php artisan manus-ai:test --task="Custom test prompt"
```

Manage tasks:
```bash
# Create task
php artisan manus-ai:task create --prompt="Your prompt" --profile=manus-1.6

# List tasks
php artisan manus-ai:task list --limit=10 --status=completed

# Get task details
php artisan manus-ai:task get --id=task_123

# Update task
php artisan manus-ai:task update --id=task_123 --title="New Title"

# Delete task
php artisan manus-ai:task delete --id=task_123
```

## 📚 API Reference

### ManusAIClient Methods

#### Task Methods
- `createTask(string $prompt, array $options = []): array`
- `getTasks(array $filters = []): array`
- `getTask(string $taskId): array`
- `updateTask(string $taskId, array $updates): array`
- `deleteTask(string $taskId): array`

#### File Methods
- `createFile(string $filename): array`
- `uploadFileContent(string $uploadUrl, string $fileContent, string $contentType): bool`
- `listFiles(): array`
- `getFile(string $fileId): array`
- `deleteFile(string $fileId): array`

#### Webhook Methods
- `createWebhook(array $webhook): array`
- `deleteWebhook(string $webhookId): bool`

### Helper Classes

#### TaskAttachment
- `TaskAttachment::fromFileId(string $fileId): array`
- `TaskAttachment::fromUrl(string $url): array`
- `TaskAttachment::fromBase64(string $data, string $mimeType): array`
- `TaskAttachment::fromFilePath(string $path): array`

#### AgentProfile
- `AgentProfile::MANUS_1_6` - Latest model (recommended)
- `AgentProfile::MANUS_1_6_LITE` - Lightweight version
- `AgentProfile::MANUS_1_6_MAX` - Maximum capability version
- `AgentProfile::SPEED` - Deprecated
- `AgentProfile::QUALITY` - Deprecated
- `AgentProfile::all(): array` - Get all profiles
- `AgentProfile::recommended(): array` - Get recommended profiles
- `AgentProfile::isValid(string $profile): bool`
- `AgentProfile::isDeprecated(string $profile): bool`

#### WebhookHandler
- `WebhookHandler::parsePayload(string $json): array`
- `WebhookHandler::isTaskCreated(array $payload): bool`
- `WebhookHandler::isTaskStopped(array $payload): bool`
- `WebhookHandler::isTaskCompleted(array $payload): bool`
- `WebhookHandler::isTaskAskingForInput(array $payload): bool`
- `WebhookHandler::getTaskDetail(array $payload): ?array`
- `WebhookHandler::getAttachments(array $payload): array`

## 💡 Examples

See the `examples/` directory for complete working examples:

- `basic.php` - Basic task creation and management
- `file-upload.php` - File upload with attachments
- `webhook.php` - Webhook setup and handling
- `laravel-routes.php` - Laravel route examples

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- [Manus AI Official Website](https://manus.ai)
- [API Documentation](https://open.manus.ai/docs)
- [GitHub Repository](https://github.com/tigusigalpa/manus-ai-php)
- [Issue Tracker](https://github.com/tigusigalpa/manus-ai-php/issues)

## 👤 Author

**Igor Sazonov**
- GitHub: [@tigusigalpa](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## 🙏 Acknowledgments

- Thanks to the Manus AI team for providing an excellent AI agent platform
- Built with inspiration from other quality PHP SDKs

---

Made with ❤️ by [Igor Sazonov](https://github.com/tigusigalpa)

