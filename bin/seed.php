<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Seeder\PlanSeeder;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Helper to get env with fallback to system getenv()
$getEnvVar = function($key) {
    return $_ENV[$key] ?? getenv($key);
};

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $getEnvVar('DB_CONNECTION') ?: 'mysql',
    'host'      => $getEnvVar('DB_HOST'),
    'database'  => $getEnvVar('DB_DATABASE'),
    'username'  => $getEnvVar('DB_USERNAME'),
    'password'  => $getEnvVar('DB_PASSWORD'),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'options'   => [
        PDO::ATTR_TIMEOUT => 5, // Prevent infinite hangs
    ],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

PlanSeeder::run();

echo "Seeding completed!\n";