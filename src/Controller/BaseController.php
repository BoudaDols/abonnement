<?php

namespace App\Controller;

use App\Service\KafkaProducer;
use Psr\Log\LoggerInterface;

abstract class BaseController
{
    protected KafkaProducer $kafka;

    public function __construct(protected LoggerInterface $logger)
    {
        $this->kafka = new KafkaProducer();
    }

    protected function json(mixed $data, int $status = 200): string
    {
        http_response_code($status);
        return json_encode($data);
    }

    protected function success(mixed $data): string
    {
        return $this->json($data);
    }

    protected function error(string $message, int $status): string
    {
        return $this->json(['error' => $message], $status);
    }

    protected function getInput(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($contentType, 'application/json')) {
            return [];
        }

        $maxSize = 1024 * 1024; // 1MB limit
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > $maxSize) {
            return [];
        }

        $raw = file_get_contents('php://input', length: $maxSize);
        if ($raw === false) {
            return [];
        }
        $raw = str_replace('\0', '', $raw); // strip null bytes
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
