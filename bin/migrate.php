<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Service\LoggerFactory;

// Charger .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$logger = (new LoggerFactory())->createLogger('migration');

// Create database if not exists
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD']
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_ENV['DB_DATABASE']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $logger->info("Database `{$_ENV['DB_DATABASE']}` ready");
} catch (PDOException $e) {
    $logger->error("Failed to create database: " . $e->getMessage());
    exit(1);
}

// Config DB
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Auto-découverte des migrations
$migrationDir = __DIR__ . '/../src/Migration';
$migrations = [];

if (is_dir($migrationDir)) {
    $files = glob($migrationDir . '/*.php');
    sort($files);
    foreach ($files as $file) {
        require_once $file;
        $className = 'App\\Migration\\' . preg_replace('/^\d+_/', '', basename($file, '.php'));
        if (class_exists($className)) {
            $migrations[] = $className;
        }
    }
}

// Migrations are already ordered by filename prefix, no need to sort again

$logger->info('Starting migrations');

foreach ($migrations as $migrationClass) {
    $migrationClass::up();
    $logger->info("Migration executed: {$migrationClass}");
    echo "Migration {$migrationClass} exécutée.\n";
}

$logger->info('Migrations completed');
echo "Migration terminée !\n";