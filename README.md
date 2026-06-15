# Manus AI PHP SDK

<div align="center">
  <img src="https://github.com/user-attachments/assets/eb30d0e5-3d22-4edb-bb42-dd4e751b5cf4" alt="Manus AI PHP SDK" style="max-width: 100%; height: auto;">
</div>

<div align="center">

**PHP SDK for [Manus AI](https://manus.ai) API v2 — works standalone or with Laravel**

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-8%7C9%7C10%7C11%7C12-orange)](https://laravel.com/)

</div>

---

> **⚠️ Breaking Changes:** Version 2.0+ uses Manus API v2 with significant changes. See [Migration Guide](#migration-from-v1) below.

## Overview

A PHP SDK for the [Manus AI](https://manus.ai) platform API v2. Covers the full API: task management, multi-turn conversations, file uploads, webhooks, projects, skills, and connectors. Includes Laravel service provider, facade, and Artisan commands out of the box.

Built on PHP 8.2+ with type hints, tested with PHPUnit, follows PSR-4/PSR-12.

### Use cases

- **Enterprise apps** — error handling, type safety, structured responses
- **Chatbots** — conversational interfaces via the chat task mode
- **Data processing** — document analysis, report generation, data extraction
- **Workflow automation** — delegate repetitive tasks to the AI agent
- **SaaS products** — embed AI features into your platform
- **Education** — AI-assisted learning tools
- **Mobile backends** — server-side AI processing for mobile apps

## Table of Contents

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

## Features

### Core

- **Full Manus AI API v2 coverage** — all endpoints implemented
- Task management — create, monitor, update, delete tasks with new message format
- **Multi-turn conversations** — `sendMessage()` for interactive dialogues
- **Task lifecycle** — `listMessages()` to poll progress, `stopTask()`, `confirmAction()`
- File handling — upload, attach (file_id, file_url, file_data), manage
- Webhooks — receive real-time notifications on task lifecycle events
- **Projects & Skills** — group tasks, enable specific skills
- **Connectors** — integrate external services
- Multi-model support — Manus 1.6, Lite, and Max

### New in v2

- **Message-based API**: Structured content format for tasks
- **Task polling**: Track progress with event messages
- **Interactive tasks**: Confirm actions when agent needs approval
- **Enhanced metadata**: `agent_status`, `share_visibility`, Unix timestamps
- **Cursor pagination**: Efficient pagination for tasks and files
- **Skills & Connectors**: Per-task configuration
- **Projects**: Organize related tasks

### Laravel

- Service provider, facade, dependency injection
- Artisan commands for task management
- Config publishing via `vendor:publish`
- API key management through `.env`

### Quality

- Error handling with detailed exception messages
- Full PHP 8.2+ type hints (IDE autocomplete, static analysis)
- PHPUnit test suite
- PSR-4 autoloading, PSR-12 code style

### Developer experience

- Documented methods with examples
- Ready-to-run example scripts
- Helper classes for attachments, agent profiles, webhooks

## Requirements

- PHP 8.2 or higher
- Composer
- Guzzle HTTP client (^7.8)
- Laravel 8+ (optional, for Laravel integration)

## Installation

### 1. Install via Composer

```bash
composer require tigusigalpa/manus-ai-php
```

### 2. Get your API key

1. Visit [Manus AI](https://manus.im) and create an account
2. Go to [API Integration Settings](http://manus.im/app?show_settings=integrations&app_name=api)
3. Copy your API key

### 3. Quick start

```php
<?php
require 'vendor/autoload.php';

use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient('your-api-key-here');

$result = $client->createTask('Write a creative story about a PHP developer who discovers AI');

echo "Task ID: {$result['task_id']}\n";
echo "View Task: {$result['task_url']}\n";
```

### Laravel quick start

The service provider is registered automatically via package discovery:

```bash
composer require tigusigalpa/manus-ai-php

# Add your API key to .env
echo "MANUS_AI_API_KEY=your-api-key-here" >> .env

# Test the connection
php artisan manus-ai:test
```

## Configuration

### PHP

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$apiKey = 'your-api-key-here';
$client = new ManusAIClient($apiKey);
```

### Laravel

#### 1. Publish config (optional)

```bash
php artisan vendor:publish --tag=manus-ai-config
```

#### 2. Add to `.env`

```env
MANUS_AI_API_KEY=your-api-key-here
MANUS_AI_DEFAULT_AGENT_PROFILE=manus-1.6
MANUS_AI_DEFAULT_TASK_MODE=agent
```

## Usage

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

Tasks are the main entity in the Manus AI API. Each task goes through a lifecycle: creation → execution → completion.

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

File uploads use a two-step process: create a file record to get a presigned URL, then upload content to that URL. After uploading, attach files to tasks.

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

Instead of polling, Manus AI sends HTTP POST requests to your endpoint on task events:

- **task_created** — fired when a task is created via the API
- **task_stopped** — fired when a task completes or requires user input

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

## API Reference

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

## Examples by use case

### Content generation

**Blog post creation**

```php
$task = $client->createTask(
    'Write a comprehensive 1500-word blog post about the benefits of AI in e-commerce, 
    including SEO keywords, meta description, and engaging headlines',
    ['agentProfile' => AgentProfile::MANUS_1_6]
);
```

**Social media posts**

```php
$task = $client->createTask(
    'Create 10 engaging social media posts for a tech startup launching a new SaaS product. 
    Include hashtags and call-to-action for each platform: Twitter, LinkedIn, and Instagram'
);
```

### Data analysis

**Report generation**

```php
// Upload sales data
$fileResult = $client->createFile('sales_data_q4.xlsx');
$client->uploadFileContent($fileResult['upload_url'], $fileContent, 'application/vnd.ms-excel');

// Analyze and generate report
$task = $client->createTask(
    'Analyze this sales data and create a comprehensive quarterly report with insights, 
    trends, and recommendations for Q1 strategy',
    ['attachments' => [TaskAttachment::fromFileId($fileResult['id'])]]
);
```

**Feedback analysis**

```php
$task = $client->createTask(
    'Analyze 500+ customer reviews and provide: sentiment analysis, common pain points, 
    feature requests, and actionable recommendations for product improvement'
);
```

### Customer support

**Ticket response**

```php
// In your support ticket system
$ticketContent = $ticket->getDescription();
$task = ManusAI::createTask(
    "Generate a professional, empathetic response to this customer support ticket: {$ticketContent}. 
    Include troubleshooting steps and escalation options if needed."
);
```

**FAQ generation**

```php
$task = $client->createTask(
    'Based on our product documentation, create a comprehensive FAQ section with 20 
    common questions and detailed answers for our help center'
);
```

### E-commerce & Product Management

**Product Description Writer**

```php
$task = $client->createTask(
    'Write compelling, SEO-optimized product descriptions for: ' . json_encode($products) . 
    '. Include features, benefits, specifications, and persuasive call-to-action.'
);
```

**Competitor Analysis**

```php
$task = $client->createTask(
    'Research and analyze top 5 competitors in the project management software space. 
    Provide pricing comparison, feature matrix, and market positioning insights.'
);
```

### Education

**Quiz Generation**

```php
$task = $client->createTask(
    'Create a 25-question multiple-choice quiz on PHP 8.2 features with explanations 
    for each answer. Include beginner, intermediate, and advanced difficulty levels.'
);
```

**Learning Paths**

```php
$task = $client->createTask(
    "Based on student's current skill level in {$subject}, create a personalized 
    12-week learning roadmap with weekly goals, resources, and practice exercises."
);
```

### Development

**Code Review**

```php
$fileResult = $client->createFile('legacy_code.php');
$client->uploadFileContent($fileResult['upload_url'], $codeContent, 'text/plain');

$task = $client->createTask(
    'Review this PHP code for: security vulnerabilities, performance issues, 
    best practices violations, and suggest improvements with code examples',
    ['attachments' => [TaskAttachment::fromFileId($fileResult['id'])]]
);
```

**API Documentation**

```php
$task = $client->createTask(
    'Generate comprehensive API documentation for our REST endpoints including: 
    request/response examples, authentication, error codes, and usage examples'
);
```

### Document Processing

**Medical Report Summarization**

```php
$task = $client->createTask(
    'Summarize this medical report into patient-friendly language, highlighting 
    key findings, recommendations, and next steps',
    ['attachments' => [TaskAttachment::fromFilePath('/path/to/medical_report.pdf')]]
);
```

**Contract Analysis**

```php
$task = $client->createTask(
    'Analyze this contract and identify: key terms, obligations, potential risks, 
    important dates, and clauses that require legal review',
    ['attachments' => [TaskAttachment::fromFileId($contractFileId)]]
);
```

### Multilingual

**Translation**

```php
$task = $client->createTask(
    'Translate this marketing copy into Spanish, French, German, and Japanese. 
    Maintain tone, cultural nuances, and marketing impact in each language.'
);
```

### Email Campaigns

**Drip Campaign**

```php
$task = $client->createTask(
    'Create a 5-email drip campaign for new SaaS users: welcome email, feature 
    highlights, success stories, upgrade incentive, and feedback request. 
    Include subject lines and optimal sending schedule.'
);
```

## Code Examples

See the `examples/` directory for complete working examples:

- `basic.php` - Basic task creation and management
- `file-upload.php` - File upload with attachments
- `webhook.php` - Webhook setup and handling
- `laravel-routes.php` - Laravel route examples
- `advanced-workflows.php` - Complex multi-step AI workflows
- `error-handling.php` - Comprehensive error handling patterns

## Testing

### Running tests

```bash
# Run all tests
composer test

# Run tests with detailed output
composer test -- --verbose

# Run specific test file
./vendor/bin/phpunit tests/ManusAIClientTest.php
```

### Code coverage

```bash
# Generate HTML coverage report
composer test-coverage

# View coverage in terminal
composer test -- --coverage-text
```

### CI matrix

- PHP 8.2, 8.3, 8.4
- Laravel 8.x, 9.x, 10.x, 11.x, 12.x
- Multiple operating systems (Linux, macOS, Windows)

## FAQ

### General Questions

**Q: Is this SDK free to use?**  
A: The SDK is open-source (MIT). You'll need a Manus AI account and API key, which may have usage costs depending on your plan.

**Q: What's the difference between this SDK and calling the API directly?**  
A: Type-safe interfaces, automatic error handling, helper classes, Laravel integration, and documentation. Saves a lot of boilerplate.

**Q: Can I use this in production?**  
A: Yes. The SDK includes error handling and logging suitable for production use.

**Q: Does this work with older PHP versions?**  
A: The SDK requires PHP 8.2+ to take advantage of modern PHP features like typed properties, enums, and improved type
system.

### Laravel-Specific Questions

**Q: Do I need Laravel to use this SDK?**  
A: No. The SDK works in any PHP 8.2+ application. Laravel integration is optional.

**Q: How do I use this in Laravel Lumen?**  
A: Register the service provider manually in `bootstrap/app.php`:

```php
$app->register(Tigusigalpa\ManusAI\Laravel\ManusAIServiceProvider::class);
```

**Q: Can I use multiple API keys in one Laravel application?**  
A: Yes, create multiple client instances:

```php
$client1 = new ManusAIClient('key-1');
$client2 = new ManusAIClient('key-2');
```

### Technical Questions

**Q: How do I handle rate limiting?**  
A: The SDK automatically includes retry logic for rate limit errors. You can also implement exponential backoff in your
application layer.

**Q: What file formats are supported for uploads?**  
A: Manus AI supports PDF, images (JPG, PNG, GIF), documents (DOC, DOCX), spreadsheets (XLS, XLSX), and text files. Check
the official API documentation for the complete list.

**Q: How long do tasks take to complete?**  
A: Task completion time varies based on complexity and model used. Simple tasks may complete in seconds, while complex
analysis can take minutes. Use webhooks for real-time notifications.

**Q: Can I cancel a running task?**  
A: Currently, the Manus AI API doesn't support task cancellation. However, you can delete completed tasks to clean up
your task list.

**Q: How do I handle errors and exceptions?**  
A: The SDK throws descriptive exceptions for all error conditions. Wrap your calls in try-catch blocks:

```php
try {
    $task = $client->createTask('Your prompt');
} catch (\Exception $e) {
    Log::error('Manus AI Error: ' . $e->getMessage());
}
```

### Performance & Optimization

**Q: Which agent profile should I use?**  
A:

- **MANUS_1_6**: Best balance of quality and speed (recommended for most use cases)
- **MANUS_1_6_LITE**: Faster responses, lower cost (good for simple tasks)
- **MANUS_1_6_MAX**: Maximum capability (complex reasoning and analysis)

**Q: How can I reduce API costs?**  
A: Use the Lite model for simple tasks, batch similar requests, cache results when appropriate, and use precise prompts
to avoid unnecessary iterations.

**Q: Can I process multiple tasks in parallel?**  
A: Yes. Create multiple tasks and use webhooks to track completion:

```php
$tasks = [];
foreach ($prompts as $prompt) {
    $tasks[] = $client->createTask($prompt);
}
```

### Integration Questions

**Q: Can I integrate this with my existing CMS?**  
A: Yes. The SDK is framework-agnostic and works with WordPress, Drupal, Joomla, or any PHP-based CMS.

**Q: Does this work with queued jobs?**  
A: Yes. Works well with Laravel queues:

```php
dispatch(new ProcessAITask($prompt));
```

**Q: Can I use this in a REST API?**  
A: Yes. See the Laravel route examples for implementation patterns.

## Troubleshooting

#### Issue: "Invalid API Key" Error

**Solution:**

1. Verify your API key is correct in `.env` or configuration
2. Ensure there are no extra spaces or quotes around the key
3. Check that your Manus AI account is active
4. Regenerate your API key from the Manus AI dashboard

```php
// Test your API key
try {
    $client = new ManusAIClient('your-api-key');
    $tasks = $client->getTasks(['limit' => 1]);
    echo "✅ API key is valid!\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

#### Issue: File Upload Fails

**Solution:**

1. Check file size limits (typically 10MB max)
2. Verify the file exists and is readable
3. Ensure correct MIME type is specified
4. Check your internet connection

```php
// Debug file upload
$filePath = '/path/to/file.pdf';
if (!file_exists($filePath)) {
    die("File not found!");
}
if (!is_readable($filePath)) {
    die("File not readable!");
}
echo "File size: " . filesize($filePath) . " bytes\n";
```

#### Issue: Webhook Not Receiving Events

**Solution:**

1. Ensure your webhook URL is publicly accessible (not localhost)
2. Verify HTTPS is enabled (required for production)
3. Check your server logs for incoming requests
4. Test webhook endpoint manually with curl

```bash
# Test webhook endpoint
curl -X POST https://your-domain.com/webhook/manus-ai \
  -H "Content-Type: application/json" \
  -d '{"event":"task_stopped","data":{"task_id":"test"}}'
```

#### Issue: Task Takes Too Long

**Solution:**

1. Use MANUS_1_6_LITE for faster responses
2. Simplify your prompt to be more specific
3. Break complex tasks into smaller subtasks
4. Implement webhooks instead of polling

#### Issue: Composer Installation Fails

**Solution:**

```bash
# Clear Composer cache
composer clear-cache

# Update Composer itself
composer self-update

# Install with verbose output
composer require tigusigalpa/manus-ai-php -vvv

# Check PHP version
php -v  # Must be 8.2 or higher
```

#### Issue: Laravel Service Provider Not Loading

**Solution:**

```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Re-publish configuration
php artisan vendor:publish --tag=manus-ai-config --force

# Verify provider is registered
php artisan about
```

### Getting Help

If you encounter issues not covered here:

1. **Check the Examples**: Review the `examples/` directory for working code
2. **Read API Docs**: Visit [Manus AI API Documentation](https://open.manus.ai/docs)
3. **Search Issues**: Check [GitHub Issues](https://github.com/tigusigalpa/manus-ai-php/issues)
4. **Ask the Community**: Open a new issue with detailed information
5. **Contact Support**: Email sovletig@gmail.com for direct assistance

### Debug Mode

Enable detailed logging for troubleshooting:

```php
// Enable Guzzle debug mode
$client = new ManusAIClient('your-api-key', [
    'debug' => true,  // Outputs all HTTP requests/responses
]);
```

## Performance & best practices

#### 1. Choose the Right Model

```php
// For simple tasks - use Lite (faster, cheaper)
$task = $client->createTask('Summarize this text in 3 sentences', [
    'agentProfile' => AgentProfile::MANUS_1_6_LITE
]);

// For complex analysis - use Max (more capable)
$task = $client->createTask('Perform deep competitive analysis with market insights', [
    'agentProfile' => AgentProfile::MANUS_1_6_MAX
]);

// For balanced performance - use standard (recommended)
$task = $client->createTask('Generate blog post with SEO optimization', [
    'agentProfile' => AgentProfile::MANUS_1_6
]);
```

#### 2. Write Effective Prompts

**❌ Poor Prompt:**

```php
$task = $client->createTask('Write something about AI');
```

**✅ Excellent Prompt:**

```php
$task = $client->createTask(
    'Write a 500-word blog post about AI in healthcare. 
    Target audience: medical professionals. 
    Tone: professional yet accessible. 
    Include: 3 real-world examples, statistics, and a call-to-action. 
    SEO keywords: AI healthcare, medical AI, diagnostic automation.'
);
```

#### 3. Implement Caching

```php
// Cache frequently requested content
$cacheKey = 'manus_task_' . md5($prompt);

if (Cache::has($cacheKey)) {
    return Cache::get($cacheKey);
}

$result = $client->createTask($prompt);
Cache::put($cacheKey, $result, now()->addHours(24));
```

#### 4. Use Webhooks for Long-Running Tasks

```php
// ❌ Polling (inefficient)
do {
    sleep(5);
    $task = $client->getTask($taskId);
} while ($task['status'] !== 'completed');

// ✅ Webhooks (efficient)
$webhook = $client->createWebhook([
    'url' => 'https://your-app.com/webhook',
    'events' => ['task_stopped']
]);
```

#### 5. Batch Processing

```php
// Process multiple items efficiently
$taskIds = [];
foreach ($items as $item) {
    $result = $client->createTask("Process: {$item}");
    $taskIds[] = $result['task_id'];
}

// Handle results via webhook or batch retrieval
```

#### 6. Error Handling & Retries

```php
use Illuminate\Support\Facades\Retry;

$task = Retry::times(3)
    ->sleep(1000)
    ->catch(function ($e) {
        Log::warning('Manus AI retry: ' . $e->getMessage());
    })
    ->throw(function ($e) {
        Log::error('Manus AI failed after retries: ' . $e->getMessage());
    })
    ->call(function () use ($client, $prompt) {
        return $client->createTask($prompt);
    });
```

#### 7. Monitor Credit Usage

```php
// Track API costs
$task = $client->getTask($taskId);
$creditsUsed = $task['credit_usage'];

Log::info("Task {$taskId} used {$creditsUsed} credits");

// Alert on high usage
if ($creditsUsed > 100) {
    Notification::send($admin, new HighCreditUsageAlert($taskId));
}
```

### Security Best Practices

#### 1. Secure API Key Storage

```php
// ✅ Use environment variables
$client = new ManusAIClient(env('MANUS_AI_API_KEY'));

// ❌ Never hardcode
$client = new ManusAIClient('sk-abc123...'); // DON'T DO THIS!
```

#### 2. Validate User Input

```php
// Sanitize prompts from user input
$userPrompt = strip_tags($request->input('prompt'));
$userPrompt = substr($userPrompt, 0, 5000); // Limit length

$task = $client->createTask($userPrompt);
```

#### 3. Implement Rate Limiting

```php
// Laravel rate limiting
Route::middleware('throttle:10,1')->post('/api/ai-task', function (Request $request) {
    return ManusAI::createTask($request->input('prompt'));
});
```

#### 4. Webhook Signature Verification

```php
// Verify webhook authenticity (implement based on your security needs)
public function handleWebhook(Request $request)
{
    $signature = $request->header('X-Manus-Signature');
    
    if (!$this->verifySignature($signature, $request->getContent())) {
        abort(403, 'Invalid signature');
    }
    
    // Process webhook...
}
```

## Contributing

### How to Contribute

#### 1. Fork & Clone

```bash
# Fork the repository on GitHub, then:
git clone https://github.com/YOUR-USERNAME/manus-ai-php.git
cd manus-ai-php
composer install
```

#### 2. Create a Feature Branch

```bash
git checkout -b feature/amazing-new-feature
# or
git checkout -b bugfix/fix-important-issue
```

#### 3. Make Your Changes

- Write clean, documented code
- Follow PSR-12 coding standards
- Add tests for new features
- Update documentation as needed

#### 4. Run Tests

```bash
# Ensure all tests pass
composer test

# Check code style
composer phpcs

# Fix code style automatically
composer phpcbf
```

#### 5. Commit Your Changes

```bash
git add .
git commit -m "feat: Add amazing new feature"

# Use conventional commit messages:
# feat: New feature
# fix: Bug fix
# docs: Documentation changes
# test: Test additions/changes
# refactor: Code refactoring
```

#### 6. Push & Create Pull Request

```bash
git push origin feature/amazing-new-feature
```

Then open a Pull Request on GitHub with:

- Clear description of changes
- Link to related issues
- Screenshots (if applicable)
- Test results

### Contribution Guidelines

#### Code Standards

- **PHP 8.2+**: Use modern PHP features (typed properties, enums, etc.)
- **Type Safety**: Always use type hints and return types
- **Documentation**: PHPDoc blocks for all public methods
- **Testing**: Maintain 100% code coverage for new features
- **PSR-12**: Follow PHP-FIG coding standards

#### What we're looking for

- Bug fixes with test cases
- Performance improvements
- Documentation improvements
- New helper methods
- Additional examples
- Streaming response support
- Symfony bundle support

#### Code review process

1. CI/CD runs tests and code quality checks
2. Maintainer reviews code and provides feedback
3. Address feedback, update PR
4. Merge

### Development Setup

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Add your test API key
echo "MANUS_AI_API_KEY=your-test-key" >> .env

# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Check code style
composer phpcs

# Fix code style
composer phpcbf
```

### Running Examples

```bash
# Test basic functionality
php examples/basic.php

# Test file uploads
php examples/file-upload.php

# Test webhooks
php examples/webhook.php
```

### Questions?

- [Discussions](https://github.com/tigusigalpa/manus-ai-php/discussions)
- [Issues](https://github.com/tigusigalpa/manus-ai-php/issues)
- Email: sovletig@gmail.com

## Roadmap

### v2.0 (planned)

**Q1 2026:**

- Streaming responses
- Async/Promise support (ReactPHP)
- Usage tracking and analytics
- Response formatters (JSON, Markdown, structured output)

**Q2 2026:**

- Symfony bundle
- WordPress plugin
- Plugin system for custom integrations

**Q3 2026:**

- Visual workflow builder
- Performance dashboard
- OAuth support
- Multi-region support

### Community requests

- [ ] GraphQL API support
- [ ] Batch file upload optimization
- [ ] Custom model fine-tuning integration
- [ ] Prompt templates library
- [ ] CMS platform integrations
- [ ] Docker container for local development

[Open a feature request](https://github.com/tigusigalpa/manus-ai-php/issues/new?template=feature_request.md) or vote on existing proposals.

## Comparison with direct API calls

| Feature                | This SDK              | Direct API Calls |
|------------------------|-----------------------|------------------|
| **Type Safety**        | Full PHP 8.2+ types   | Manual           |
| **Laravel Integration**| Built-in              | Manual           |
| **Error Handling**     | Automatic             | Manual           |
| **Helper Classes**     | Attachments, webhooks | None             |
| **Test Coverage**      | PHPUnit suite         | N/A              |
| **Webhook Helpers**    | Built-in              | Manual           |

## License

MIT License — see [LICENSE](LICENSE).

## Links

- [Manus AI Website](https://manus.ai)
- [API v2 Documentation](https://open.manus.im/docs/v2/introduction)
- [Get API Key](http://manus.im/app?show_settings=integrations&app_name=api)
- [GitHub Repository](https://github.com/tigusigalpa/manus-ai-php)
- [Packagist](https://packagist.org/packages/tigusigalpa/manus-ai-php)
- [Issue Tracker](https://github.com/tigusigalpa/manus-ai-php/issues)
- [Discussions](https://github.com/tigusigalpa/manus-ai-php/discussions)
- [Changelog](https://github.com/tigusigalpa/manus-ai-php/blob/main/CHANGELOG.md)

## Migration from v1

### Breaking Changes

1. **API Endpoints**: All endpoints changed from `/v1/` to `/v2/` with new naming (e.g., `/v2/task.create`)

2. **Authentication Header**: Changed from `Authorization` to `x-manus-api-key`

3. **Request Structure**: Tasks now use message-based format:
   ```php
   // v1
   $client->createTask('Hello', [
       'agentProfile' => 'manus-1.6',
       'taskMode' => 'agent',
   ]);
   
   // v2
   $client->createTask('Hello', [
       'agent_profile' => 'manus-1.6',
       'share_visibility' => 'private',
   ]);
   ```

4. **Response Format**: All responses now include `ok` and `request_id` fields

5. **Field Names**: Snake_case preferred (both camelCase and snake_case supported for backward compatibility):
   - `agentProfile` → `agent_profile`
   - `hideInTaskList` → `hide_in_task_list`
   - `createShareableLink` → `share_visibility`

6. **Response Keys**: Changed field names in responses:
   - `status` → `agent_status`
   - `data` → `tasks` (in list responses)
   - `id` → `file_id` (for files)

7. **Timestamps**: Changed from ISO strings to Unix milliseconds (integers)

8. **Attachments**: New structure with `file_id`, `file_url`, `file_data`

9. **Removed Fields**:
   - `taskMode` (no longer needed)
   - `createShareableLink` (replaced by `share_visibility`)

10. **New Methods**:
    - `listMessages()` - Poll task progress
    - `sendMessage()` - Continue conversations
    - `stopTask()` - Stop running tasks
    - `confirmAction()` - Confirm pending actions

### Migration Example

```php
// v1
$task = $client->createTask('Hello', [
    'agentProfile' => 'manus-1.6',
    'taskMode' => 'agent',
]);

// v2
$task = $client->createTask('Hello', [
    'agent_profile' => 'manus-1.6',
    'share_visibility' => 'private',
]);

// v2: Poll for progress
$messages = $client->listMessages($task['task_id']);

// v2: Continue conversation
$client->sendMessage($task['task_id'], 'Tell me more');
```

### Backward Compatibility

The SDK accepts both camelCase and snake_case for input parameters:

```php
// Both work
$client->createTask('Hello', ['agentProfile' => 'manus-1.6']);
$client->createTask('Hello', ['agent_profile' => 'manus-1.6']);
```

However, **response keys use the new v2 format** (snake_case and new field names).

## Author

**Igor Sazonov**
- GitHub: [@tigusigalpa](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## Acknowledgments

- **Manus AI Team** — for the platform and API documentation
- **Contributors** — pull requests, issues, and documentation improvements

### Built with

- PHP 8.2+
- Guzzle HTTP
- Laravel (optional)
- PHPUnit
- PHPStan
- PHP-CS-Fixer

