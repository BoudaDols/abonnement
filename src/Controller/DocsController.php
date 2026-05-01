<?php

namespace App\Controller;

class DocsController extends BaseController
{
    public function index(): string
    {
        $path = realpath(__DIR__ . '/../../public/docs.html');
        if (!$path || !str_starts_with($path, realpath(__DIR__ . '/../../public'))) {
            return $this->error('Not Found', 404);
        }
        header('Content-Type: text/html');
        readfile($path);
        return '';
    }

    public function spec(): string
    {
        $path = realpath(__DIR__ . '/../../openapi.yaml');
        if (!$path || !str_starts_with($path, realpath(__DIR__ . '/../..'))) {
            return $this->error('Not Found', 404);
        }
        header('Content-Type: application/yaml');
        readfile($path);
        return '';
    }
}
