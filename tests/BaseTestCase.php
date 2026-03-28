<?php

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class BaseTestCase extends TestCase
{
    protected static bool $booted = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$booted) {
            $capsule = new Capsule;
            $capsule->addConnection([
                'driver'   => 'sqlite',
                'database' => ':memory:',
            ]);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            $this->runMigrations();
            self::$booted = true;
        }
    }

    private function runMigrations(): void
    {
        $migrationDir = __DIR__ . '/../src/Migration';
        $files = glob($migrationDir . '/*.php');
        sort($files);
        foreach ($files as $file) {
            require_once $file;
            $className = 'App\\Migration\\' . preg_replace('/^\d+_/', '', basename($file, '.php'));
            if (class_exists($className)) {
                $className::up();
            }
        }
    }
}