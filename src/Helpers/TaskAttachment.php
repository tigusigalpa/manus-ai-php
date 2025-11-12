<?php

namespace Tigusigalpa\ManusAI\Helpers;

class TaskAttachment
{
    /**
     * Create a file ID attachment
     *
     * @param string $fileId File ID from createFile API
     * @return array
     */
    public static function fromFileId(string $fileId): array
    {
        return [
            'type' => 'file_id',
            'file_id' => $fileId,
        ];
    }

    /**
     * Create a URL attachment
     *
     * @param string $url Public URL of the file/image
     * @return array
     */
    public static function fromUrl(string $url): array
    {
        return [
            'type' => 'url',
            'url' => $url,
        ];
    }

    /**
     * Create a base64 data attachment
     *
     * @param string $base64Data Base64 encoded file content
     * @param string $mimeType MIME type (e.g., 'image/png', 'application/pdf')
     * @return array
     */
    public static function fromBase64(string $base64Data, string $mimeType): array
    {
        return [
            'type' => 'data',
            'data' => $base64Data,
            'mime_type' => $mimeType,
        ];
    }

    /**
     * Create attachment from local file path
     *
     * @param string $filePath Path to local file
     * @return array
     * @throws \RuntimeException
     */
    public static function fromFilePath(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Failed to read file: {$filePath}");
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $base64Data = base64_encode($content);

        return self::fromBase64($base64Data, $mimeType);
    }
}
