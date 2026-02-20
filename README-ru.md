# Manus AI PHP SDK

<div align="center">
  <img src="https://github.com/user-attachments/assets/eb30d0e5-3d22-4edb-bb42-dd4e751b5cf4" alt="Manus AI PHP SDK" style="max-width: 100%; height: auto;">
</div>

<div align="center">

**PHP SDK для платформы [Manus AI](https://manus.ai) — работает автономно или с Laravel**

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-8%7C9%7C10%7C11%7C12-orange)](https://laravel.com/)

[English Documentation](README.md) | **Русская документация**

</div>

---

## Обзор

PHP SDK для платформы [Manus AI](https://manus.ai). Покрывает весь API: управление задачами, загрузка файлов, вебхуки. Включает Laravel service provider, facade и Artisan-команды.

Построен на PHP 8.2+ с type hints, протестирован PHPUnit, соответствует PSR-4/PSR-12.

### Применение

- **Корпоративные приложения** — обработка ошибок, типобезопасность, структурированные ответы
- **Чат-боты** — диалоговые интерфейсы через режим chat
- **Обработка данных** — анализ документов, генерация отчётов, извлечение данных
- **Автоматизация** — делегирование повторяющихся задач AI-агенту
- **SaaS-платформы** — встраивание AI-функций в продукт
- **Образование** — AI-ассистированные обучающие инструменты
- **Мобильные бэкенды** — серверная AI-обработка для мобильных приложений

## Содержание

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Конфигурация](#конфигурация)
- [Использование](#использование)
  - [Базовое использование](#базовое-использование)
  - [Управление задачами](#управление-задачами)
  - [Управление файлами](#управление-файлами)
  - [Вебхуки](#вебхуки)
- [Artisan-команды](#artisan-команды)
- [Примеры](#примеры)
- [Тестирование](#тестирование)
- [Лицензия](#лицензия)

## Возможности

### Основное

- Полное покрытие API Manus AI — все эндпоинты реализованы
- Управление задачами — создание, мониторинг, обновление, удаление
- Работа с файлами — загрузка, вложения (PDF, изображения, документы)
- Вебхуки — уведомления о событиях жизненного цикла задач
- Несколько моделей — Manus 1.6, Lite и Max

### Laravel

- Service provider, facade, dependency injection
- Artisan-команды для управления задачами
- Публикация конфигурации через `vendor:publish`
- Управление API-ключами через `.env`

### Качество

- Обработка ошибок с подробными сообщениями об исключениях
- Полные type hints PHP 8.2+ (автодополнение IDE, статический анализ)
- Набор тестов PHPUnit
- PSR-4 автозагрузка, PSR-12 стиль кода

### Для разработчика

- Документированные методы с примерами
- Готовые к запуску примеры
- Вспомогательные классы для вложений, профилей агентов, вебхуков

## Требования

- PHP 8.2 или выше
- Composer
- Guzzle HTTP клиент (^7.8)
- Laravel 8+ (опционально, для интеграции с Laravel)

## Установка

### 1. Установка через Composer

```bash
composer require tigusigalpa/manus-ai-php
```

### 2. Получение API-ключа

1. Зайдите на [Manus AI](https://manus.im) и создайте аккаунт
2. Перейдите в [Настройки интеграции API](http://manus.im/app?show_settings=integrations&app_name=api)
3. Скопируйте API-ключ

### 3. Быстрый старт

```php
<?php
require 'vendor/autoload.php';

use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient('ваш-api-ключ');

$result = $client->createTask('Напиши креативную историю о PHP-разработчике, который открывает для себя AI');

echo "ID задачи: {$result['task_id']}\n";
echo "Просмотреть задачу: {$result['task_url']}\n";
```

### Быстрый старт для Laravel

Service provider регистрируется автоматически через package discovery:

```bash
composer require tigusigalpa/manus-ai-php

# Добавьте API-ключ в .env
echo "MANUS_AI_API_KEY=ваш-api-ключ" >> .env

# Проверьте подключение
php artisan manus-ai:test
```

## Конфигурация

### PHP

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$apiKey = 'ваш-api-ключ';
$client = new ManusAIClient($apiKey);
```

### Конфигурация Laravel

#### 1. Опубликовать конфигурацию (опционально)

```bash
php artisan vendor:publish --tag=manus-ai-config
```

#### 2. Добавить в `.env`

```env
MANUS_AI_API_KEY=ваш-api-ключ
MANUS_AI_DEFAULT_AGENT_PROFILE=manus-1.6
MANUS_AI_DEFAULT_TASK_MODE=agent
```

## Использование

### Базовое использование

```php
use Tigusigalpa\ManusAI\ManusAIClient;

$client = new ManusAIClient('ваш-api-ключ');

// Создать задачу
$result = $client->createTask('Напиши стихотворение о программировании на PHP', [
    'agentProfile' => 'manus-1.6',
    'taskMode' => 'chat',
]);

echo "Задача создана: {$result['task_id']}\n";
echo "Просмотреть по адресу: {$result['task_url']}\n";
```

### Управление задачами

Задачи — основная сущность в API Manus AI. Жизненный цикл задачи: создание → выполнение → завершение.

**Документация API:** [Справочник API задач](https://open.manus.ai/docs/api-reference/create-task)

#### Создание задачи

[Документация API: Создание задачи](https://open.manus.ai/docs/api-reference/create-task)

```php
use Tigusigalpa\ManusAI\Helpers\AgentProfile;

$task = $client->createTask('Ваш запрос здесь', [
    'agentProfile' => AgentProfile::MANUS_1_6,  // или AgentProfile::MANUS_1_6_LITE, MANUS_1_6_MAX
    'taskMode' => 'agent',                       // 'chat', 'adaptive', или 'agent'
    'locale' => 'ru-RU',
    'hideInTaskList' => false,
    'createShareableLink' => true,
]);
```

**Доступные профили агентов:**
- `AgentProfile::MANUS_1_6` - Основная модель (рекомендуется)
- `AgentProfile::MANUS_1_6_LITE` - Более быстрая, облегченная версия
- `AgentProfile::MANUS_1_6_MAX` - Версия с максимальными возможностями
- `AgentProfile::SPEED` - ⚠️ Устарело, используйте `MANUS_1_6_LITE` вместо этого
- `AgentProfile::QUALITY` - ⚠️ Устарело, используйте `MANUS_1_6` вместо этого

```php
// Или используйте строковые значения напрямую
$task = $client->createTask('Ваш запрос', [
    'agentProfile' => 'manus-1.6',
]);
```

#### Получение деталей задачи

[Документация API: Получение задачи](https://open.manus.ai/docs/api-reference/get-task)

```php
$task = $client->getTask('task_id');

echo "Статус: {$task['status']}\n";
echo "Использовано кредитов: {$task['credit_usage']}\n";

// Доступ к выходным сообщениям
foreach ($task['output'] as $message) {
    echo "[{$message['role']}]: {$message['content']}\n";
}
```

#### Список задач

[Документация API: Получение задач](https://open.manus.ai/docs/api-reference/get-tasks)

```php
$tasks = $client->getTasks([
    'limit' => 10,
    'order' => 'desc',
    'orderBy' => 'created_at',
    'status' => ['completed', 'running'],
]);

foreach ($tasks['data'] as $task) {
    echo "Задача {$task['id']}: {$task['status']}\n";
}
```

#### Обновление задачи

[Документация API: Обновление задачи](https://open.manus.ai/docs/api-reference/update-task)

```php
$updated = $client->updateTask('task_id', [
    'title' => 'Новое название задачи',
    'enableShared' => true,
    'enableVisibleInTaskList' => true,
]);
```

#### Удаление задачи

[Документация API: Удаление задачи](https://open.manus.ai/docs/api-reference/delete-task)

```php
$result = $client->deleteTask('task_id');
echo "Удалено: " . ($result['deleted'] ? 'да' : 'нет') . "\n";
```

### Управление файлами

Загрузка файлов — двухэтапный процесс: создание записи файла для получения presigned URL, затем загрузка контента по этому URL. После загрузки файлы можно прикрепить к задачам.

**Документация API:** [Справочник API файлов](https://open.manus.ai/docs/api-reference/create-file)

#### Загрузка файла

[Документация API: Создание файла](https://open.manus.ai/docs/api-reference/create-file)

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// 1. Создать запись файла
$fileResult = $client->createFile('document.pdf');

// 2. Загрузить содержимое файла
$fileContent = file_get_contents('/путь/к/document.pdf');
$client->uploadFileContent(
    $fileResult['upload_url'],
    $fileContent,
    'application/pdf'
);

// 3. Использовать файл в задаче
$attachment = TaskAttachment::fromFileId($fileResult['id']);

$task = $client->createTask('Проанализируй этот документ', [
    'attachments' => [$attachment],
]);
```

#### Различные типы вложений

```php
use Tigusigalpa\ManusAI\Helpers\TaskAttachment;

// Из ID файла
$attachment1 = TaskAttachment::fromFileId('file_123');

// Из URL
$attachment2 = TaskAttachment::fromUrl('https://example.com/image.jpg');

// Из base64
$attachment3 = TaskAttachment::fromBase64($base64Data, 'image/png');

// Из локального пути к файлу
$attachment4 = TaskAttachment::fromFilePath('/путь/к/файлу.pdf');
```

#### Список файлов

[Документация API: Список файлов](https://open.manus.ai/docs/api-reference/list-files)

```php
$files = $client->listFiles();

foreach ($files['data'] as $file) {
    echo "{$file['filename']} - {$file['status']}\n";
}
```

#### Удаление файла

[Документация API: Удаление файла](https://open.manus.ai/docs/api-reference/delete-file)

```php
$result = $client->deleteFile('file_id');
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

Автор: [Igor Sazonov](https://github.com/tigusigalpa)
