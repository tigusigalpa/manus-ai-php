# Manus AI PHP SDK

Полнофункциональная PHP библиотека для интеграции с API [Manus AI](https://manus.ai). Легко интегрируйте AI-агента Manus в ваши PHP приложения с полной поддержкой Laravel.

[English Documentation](README.md) | **Русская документация**

## Возможности

- ✅ Полная поддержка API Manus AI
- ✅ Создание и управление задачами
- ✅ Загрузка файлов и работа с вложениями
- ✅ Интеграция вебхуков для получения уведомлений в реальном времени
- ✅ Service Provider и Facade для Laravel
- ✅ Artisan команды для работы через CLI
- ✅ Комплексная обработка ошибок
- ✅ Типизированные интерфейсы
- ✅ PHPUnit тесты включены
- ✅ Подробная документация и примеры

## Требования

- PHP 8.2 или выше
- Composer
- Guzzle HTTP клиент (^7.8)
- Laravel 8+ (опционально, для интеграции с Laravel)

## Установка

Установите пакет через Composer:

```bash
composer require tigusigalpa/manus-ai-php
```

## Быстрый старт

### Базовое использование

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient('ваш-api-ключ');

// Создать задачу
$result = $client->createTask('Напиши стихотворение о PHP', [
    'agentProfile' => 'manus-1.6',
    'taskMode' => 'chat',
]);

echo "Задача создана: {$result['task_id']}\n";
echo "Ссылка: {$result['task_url']}\n";
```

### Laravel интеграция

```php
use Tigusigalpa\ManusAI\Laravel\ManusAI;

// Используя Facade
$result = ManusAI::createTask('Ваш запрос');

// Получить задачу
$task = ManusAI::getTask('task_id');

// Список задач
$tasks = ManusAI::getTasks(['limit' => 10]);
```

## Основные функции

### Управление задачами

```php
// Создать задачу
$task = $client->createTask('Ваш запрос', [
    'agentProfile' => 'manus-1.6',
    'taskMode' => 'agent',
    'locale' => 'ru-RU',
]);

// Получить детали задачи
$task = $client->getTask('task_id');

// Список задач
$tasks = $client->getTasks([
    'limit' => 10,
    'status' => ['completed', 'running'],
]);

// Обновить задачу
$updated = $client->updateTask('task_id', [
    'title' => 'Новое название',
]);

// Удалить задачу
$client->deleteTask('task_id');
```

### Загрузка файлов

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// Создать файл
$fileResult = $client->createFile('document.pdf');

// Загрузить содержимое
$content = file_get_contents('/путь/к/файлу.pdf');
$client->uploadFileContent(
    $fileResult['upload_url'],
    $content,
    'application/pdf'
);

// Использовать в задаче
$attachment = TaskAttachment::fromFileId($fileResult['id']);
$task = $client->createTask('Проанализируй документ', [
    'attachments' => [$attachment],
]);
```

### Вебхуки

```php
use Tigusigalpa\ManusAI\Helpers\WebhookHandler;

// Создать вебхук
$webhook = $client->createWebhook([
    'url' => 'https://ваш-домен.com/webhook',
    'events' => ['task_created', 'task_stopped'],
]);

// Обработка вебхука
$payload = WebhookHandler::parsePayload($request->getContent());

if (WebhookHandler::isTaskCompleted($payload)) {
    $detail = WebhookHandler::getTaskDetail($payload);
    echo "Задача завершена: {$detail['message']}\n";
}
```

## Artisan команды

```bash
# Тест подключения
php artisan manus-ai:test

# Создать задачу
php artisan manus-ai:task create --prompt="Ваш запрос"

# Список задач
php artisan manus-ai:task list --limit=10

# Детали задачи
php artisan manus-ai:task get --id=task_123
```

## Документация

Полная документация доступна в [README.md](README.md).

## Примеры

См. директорию `examples/` для рабочих примеров:
- `basic.php` - Базовое использование
- `file-upload.php` - Загрузка файлов
- `webhook.php` - Настройка вебхуков
- `laravel-routes.php` - Примеры для Laravel

## Тестирование

```bash
composer test
```

## Лицензия

MIT License. См. [LICENSE](LICENSE) для деталей.

## Автор

**Igor Sazonov**
- GitHub: [@tigusigalpa](https://github.com/tigusigalpa)
- Email: sovletig@gmail.com

## Ссылки

- [Сайт Manus AI](https://manus.ai)
- [Документация API](https://open.manus.ai/docs)
- [GitHub репозиторий](https://github.com/tigusigalpa/manus-ai-php)

---

Сделано с ❤️ [Igor Sazonov](https://github.com/tigusigalpa)
