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
            self::$booted = true;
        }
    }
}