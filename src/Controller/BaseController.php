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
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}