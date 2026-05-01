<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;

abstract class BaseController
{
    public function __construct(protected LoggerInterface $logger) {}

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
        $maxSize = 1024 * 1024; // 1MB limit
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > $maxSize) {
            return [];
        }

        $raw = file_get_contents('php://input', length: $maxSize);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}