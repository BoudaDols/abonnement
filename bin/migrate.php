<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Service\LoggerFactory;

// Charger .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$logger = (new LoggerFactory())->createLogger('migration');

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
    foreach (glob($migrationDir . '/*.php') as $file) {
        $className = 'App\\Migration\\' . basename($file, '.php');
        if (class_exists($className)) {
            $migrations[] = $className;
        }
    }
}

// Trier les migrations par nom de fichier
sort($migrations);

$logger->info('Starting migrations');

foreach ($migrations as $migrationClass) {
    $migrationClass::up();
    $logger->info("Migration executed: {$migrationClass}");
    echo "Migration {$migrationClass} exécutée.\n";
}

$logger->info('Migrations completed');
echo "Migration terminée !\n";