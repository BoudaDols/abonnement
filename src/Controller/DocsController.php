<?php

namespace App\Controller;

class DocsController extends BaseController
{
    public function index(): string
    {
        header('Content-Type: text/html');
        readfile(__DIR__ . '/../../public/docs.html');
        return '';
    }

    public function spec(): string
    {
        header('Content-Type: application/yaml');
        readfile(__DIR__ . '/../../openapi.yaml');
        return '';
    }
}
