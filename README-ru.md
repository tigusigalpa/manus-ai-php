# Manus AI PHP/Laravel Client/SDK/Library

![Manus AI PHP Laravel SDK Client](https://i.postimg.cc/N09nSQy3/manus-ai-php-laravel-sdk-client.jpg)

[![Тесты](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/tests.yml)
[![Покрытие](https://codecov.io/gh/tigusigalpa/manus-ai-php/branch/main/graph/badge.svg)](https://app.codecov.io/gh/tigusigalpa/manus-ai-php)
[![CodeQL](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/codeql.yml/badge.svg?branch=main)](https://github.com/tigusigalpa/manus-ai-php/actions/workflows/codeql.yml)
[![Стабильная версия](https://poser.pugx.org/tigusigalpa/manus-ai-php/v/stable)](https://packagist.org/packages/tigusigalpa/manus-ai-php)
[![Версия PHP](https://img.shields.io/packagist/php-v/tigusigalpa/manus-ai-php)](https://packagist.org/packages/tigusigalpa/manus-ai-php)
[![Лицензия](https://poser.pugx.org/tigusigalpa/manus-ai-php/license)](https://github.com/tigusigalpa/manus-ai-php/blob/main/LICENSE)

Независимый PHP 8.2+ клиент для [Manus API v2](https://open.manus.im/docs/v2/introduction) с опциональной интеграцией Laravel. Он создаёт и управляет задачами агентов, загружает файлы, помогает обрабатывать вебхуки и покрывает discovery-, usage- и website-эндпоинты v2.

[English documentation](README.md) · [Справочник Manus API](https://open.manus.im/docs/v2/introduction) · [Go SDK](https://github.com/tigusigalpa/manus-ai-go)

Изменения выравнивания с API v2 перечислены в [CHANGELOG.md](CHANGELOG.md).

> Возможности API, профили агентов и допустимые значения определяет Manus. При обновлении интеграции сверяйтесь с официальной документацией API.

## Установка

```bash
composer require tigusigalpa/manus-ai-php
```

Нужны PHP 8.2+, Guzzle 7 и Laravel 8–12 только при использовании Laravel-интеграции.

## Быстрый старт

Не храните ключ в репозитории:

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient($_ENV['MANUS_AI_API_KEY']);

$task = $client->createTask('Напиши краткое описание релиза для этого PHP-пакета.', [
    'agent_profile' => 'standard',
    'title' => 'Описание релиза',
]);

printf("Создана задача %s: %s\n", $task['task_id'], $task['task_url']);
```

Задача выполняется асинхронно. Отслеживайте ход через `listMessages()` или вебхуки: `task.detail` возвращает только статус и метаданные.

## Аутентификация и настройка клиента

Для прямой аутентификации передайте API-ключ:

```php
$client = new ManusAIClient('ваш-api-ключ');
```

Для Manus Open Apps используйте OAuth-токен. Клиент передаёт либо `Authorization: Bearer …`, либо `x-manus-api-key`, но никогда оба заголовка:

```php
$client = new ManusAIClient('', bearerToken: 'oauth-access-token');
```

Остальные параметры конструктора позволяют задать base URL, Guzzle-клиент, таймауты и стандартные опции задач:

```php
$client = new ManusAIClient(
    apiKey: $_ENV['MANUS_AI_API_KEY'],
    timeout: 45,
    defaultTaskOptions: ['locale' => 'ru-RU', 'share_visibility' => 'private'],
);
```

## Задачи

### Создание задачи

`createTask()` принимает актуальные `snake_case`-опции API. Совместимые camelCase-алиасы остаются для старого кода, но в новом коде используйте текущие имена.

```php
use Tigusigalpa\ManusAI\Helpers\AgentProfile;
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

$task = $client->createTask('Сделай краткое содержание приложенного отчёта.', [
    'agent_profile' => AgentProfile::STANDARD, // standard, lite или max
    'locale' => 'ru-RU',
    'title' => 'Содержание отчёта',
    'project_id' => 'project_123',
    'hide_in_task_list' => false,
    'share_visibility' => 'private', // private, team или public
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

В `task_references` разрешено не более 20 идентификаторов задач: это должны быть 22-символьные ID, а не URL. `enable_ask_user` и `enableAskUser` сохранены как алиасы `interactive_mode`.

Актуальные профили: `AgentProfile::STANDARD`, `LITE`, `MAX`. `MANUS_1_6`, `MANUS_1_6_LITE` и `MANUS_1_6_MAX` оставлены как устаревшие алиасы и пока принимаются Manus для совместимости.

### Получение, список, обновление и удаление

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
    'title' => 'Новое название',
    'enable_visible_in_task_list' => true,
    'share_visibility' => 'team',
]);

$client->deleteTask($task['task_id']);
```

Нативный ответ v2 для `task.detail` содержит объект в ключе `task`. Для удобства `getTask()` сохраняет этот ключ и дублирует его поля (`id`, `status`, `agent_profile` и другие) в корневом массиве результата.

### Ход работы и продолжение диалога

```php
$messages = $client->listMessages($task['task_id'], limit: 50, order: 'desc', verbose: true);

$client->sendMessage(
    $task['task_id'],
    'Сделай содержание короче.',
    options: ['agent_profile' => 'lite'],
);

$client->stopTask($task['task_id']);
$client->confirmAction($task['task_id'], 'event_123', ['confirmed' => true]);
```

`sendMessage()` также принимает вложения и опции сообщения `connectors`, `enable_skills`, `force_skills`, `task_references`.

## Файлы и вложения

Загрузка состоит из трёх шагов: получить presigned URL, отправить байты PUT-запросом и передать `file_id` при создании задачи.

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

$file = $client->createFile('report.pdf');
$client->uploadFileContent(
    $file['upload_url'],
    file_get_contents('/path/to/report.pdf'),
    'application/pdf',
);

$attachment = TaskAttachment::fromFileId($file['file_id']);
$client->createTask('Проанализируй приложенный отчёт.', ['attachments' => [$attachment]]);
```

`createFile()` и `getFile()` сохраняют нативный объект `file`, а его поля дополнительно доступны на верхнем уровне. `file_id` добавлен как удобный алиас для `file.id`.

В `TaskAttachment` есть `fromFileId()`, `fromUrl()`, `fromBase64()` и `fromFilePath()`. Последний метод читает весь файл в память, поэтому для больших файлов используйте presigned upload.

У API v2 **нет** эндпоинта `/v2/file.list`. `listFiles()` оставлен только как устаревший метод: он бросает `ValidationException` до сетевого запроса. Сохраните ID из `createFile()`, если потом понадобится `getFile()` или `deleteFile()`.

## Вебхуки

```php
$webhook = $client->createWebhook([
    'url' => 'https://example.com/webhooks/manus',
]);

echo $webhook['webhook_id'];
```

`webhook.create` в API v2 не принимает поле `events`: Manus автоматически регистрирует уведомления жизненного цикла. Старое значение `events` игнорируется для совместимости.

```php
use Tigusigalpa\ManusAI\Helpers\WebhookHandler;

$payload = WebhookHandler::parsePayload($request->getContent());

if (WebhookHandler::isTaskCompleted($payload)) {
    $attachments = WebhookHandler::getAttachments($payload);
}
if (WebhookHandler::isTaskAskingForInput($payload)) {
    // Получите event ID через task.listMessages и вызовите confirmAction().
}
```

Для управления используйте `listWebhooks()`, `deleteWebhook()` и `getWebhookPublicKey()`.

## Дополнительные ресурсы API v2

| Раздел | Методы |
| --- | --- |
| Проекты и discovery | `createProject`, `listProjects`, `listSkills`, `listAgents`, `getAgent`, `updateAgent`, `listConnectors` |
| Браузер и вебхуки | `listOnlineBrowserClients`, `listWebhooks`, `getWebhookPublicKey` |
| Кредиты | `listUsage`, `getTeamUsageStatistic`, `listTeamUsageLog`, `getAvailableCredits` |
| Сайты задач | `getWebsiteStatus`, `listWebsiteCheckpoints`, `publishWebsite`, `updateWebsite` |

Все методы возвращают декодированный JSON-ответ v2. Допустимые значения и поля результата смотрите в [официальной документации](https://open.manus.im/docs/v2/introduction).

## Laravel

Service provider обнаруживается Laravel автоматически. При необходимости опубликуйте конфигурацию:

```bash
php artisan vendor:publish --tag=manus-ai-config
```

```env
MANUS_AI_API_KEY=ваш-api-ключ
# Или, вместо API-ключа:
# MANUS_AI_BEARER_TOKEN=oauth-access-token
MANUS_AI_DEFAULT_AGENT_PROFILE=standard
MANUS_AI_DEFAULT_LOCALE=ru-RU
MANUS_AI_SHARE_VISIBILITY=private
MANUS_AI_INTERACTIVE_MODE=false
```

Провайдер регистрирует один singleton для `ManusAIClientInterface`, `ManusAIClient` и facade `ManusAI`; таймауты из `config/manus-ai.php` применяются при его создании.

```php
use Tigusigalpa\ManusAI\Contracts\ManusAIClientInterface;
use Tigusigalpa\ManusAI\Laravel\ManusAI;

// Dependency injection
function createReleaseNote(ManusAIClientInterface $manus): array
{
    return $manus->createTask('Напиши описание релиза.');
}

// Или facade
$task = ManusAI::createTask('Напиши описание релиза.');
```

Artisan-команды:

```bash
php artisan manus-ai:test
php artisan manus-ai:task create --prompt="Напиши описание релиза" --profile=standard
php artisan manus-ai:task list --limit=10
php artisan manus-ai:task get --id=task_123
php artisan manus-ai:task update --id=task_123 --title="Новое название"
php artisan manus-ai:task delete --id=task_123
```

## Ошибки

- `AuthenticationException` — неверные учётные данные или ответ HTTP 401/403.
- `ValidationException` — некорректные параметры клиента и API-ошибка `invalid_argument`.
- `ManusAIException` — остальные транспортные, HTTP-ошибки и ошибки декодирования ответа.

Контекст исключения (`getContext()`) содержит HTTP-статус, код Manus и `request_id`, если он был передан API. Не логируйте ключи и OAuth-токены.

## Тестирование

```bash
composer install
composer test
```

Тесты используют мокированный HTTP-клиент и не обращаются к Manus.

## Миграция со старого PHP-клиента

- Уберите `taskMode`: используйте актуальные поля `interactive_mode`, `project_id`, `share_visibility`.
- Замените `enable_ask_user` на `interactive_mode`; старое имя остаётся алиасом.
- При обновлении задачи используйте `enable_visible_in_task_list` вместо `hide_in_task_list`. Старый алиас автоматически инвертируется.
- Читайте статус через `getTask($id)['status']`, а историю событий и результаты — через `listMessages()`.
- Используйте `file_id` из `createFile()` и удалите вызовы `listFiles()`.
- В новых запросах предпочитайте `standard`, `lite`, `max` для `agent_profile`.

## Лицензия

MIT. См. [LICENSE](LICENSE).
