<?php

namespace Tigusigalpa\ManusAI\Helpers;

class WebhookHandler
{
    /**
     * Verify and parse webhook payload
     *
     * @param string $jsonPayload Raw JSON payload from webhook request
     * @return array Parsed webhook data
     * @throws \InvalidArgumentException
     */
    public static function parsePayload(string $jsonPayload): array
    {
        $data = json_decode($jsonPayload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload: ' . json_last_error_msg());
        }

        if (!isset($data['event_type'])) {
            throw new \InvalidArgumentException('Missing event_type in webhook payload');
        }

        return $data;
    }

    /**
     * Check if webhook event is task_created
     *
     * @param array $payload Webhook payload
     * @return bool
     */
    public static function isTaskCreated(array $payload): bool
    {
        return ($payload['event_type'] ?? '') === 'task_created';
    }

    /**
     * Check if webhook event is task_stopped
     *
     * @param array $payload Webhook payload
     * @return bool
     */
    public static function isTaskStopped(array $payload): bool
    {
        return ($payload['event_type'] ?? '') === 'task_stopped';
    }

    /**
     * Check if task stopped with finish reason
     *
     * @param array $payload Webhook payload
     * @return bool
     */
    public static function isTaskCompleted(array $payload): bool
    {
        return self::isTaskStopped($payload) &&
               ($payload['task_detail']['stop_reason'] ?? '') === 'finish';
    }

    /**
     * Check if task stopped and requires user input
     *
     * @param array $payload Webhook payload
     * @return bool
     */
    public static function isTaskAskingForInput(array $payload): bool
    {
        return self::isTaskStopped($payload) &&
               ($payload['task_detail']['stop_reason'] ?? '') === 'ask';
    }

    /**
     * Get task details from webhook payload
     *
     * @param array $payload Webhook payload
     * @return array|null
     */
    public static function getTaskDetail(array $payload): ?array
    {
        return $payload['task_detail'] ?? null;
    }

    /**
     * Get attachments from task_stopped event
     *
     * @param array $payload Webhook payload
     * @return array
     */
    public static function getAttachments(array $payload): array
    {
        $taskDetail = self::getTaskDetail($payload);
        return $taskDetail['attachments'] ?? [];
    }
}
