<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Service\LoggerFactory;

// Charger .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$logger = (new LoggerFactory())->createLogger('migration');

try {
    // Create database if not exists
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $dbName = getenv('DB_DATABASE') ?: 'abonnement';
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');
    $rootPass = getenv('MYSQL_ROOT_PASSWORD');

    if (!$user) throw new RuntimeException("DB_USERNAME environment variable is missing.");
    if (!$rootPass) throw new RuntimeException("MYSQL_ROOT_PASSWORD environment variable is missing.");

    $pdo = new PDO(
        "mysql:host={$host};port={$port}",
        'root',
        $rootPass,
        [PDO::ATTR_TIMEOUT => 5]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $logger->info("Database `{$dbName}` ready");

    // Config DB
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => getenv('DB_CONNECTION') ?: 'mysql',
        'host'      => getenv('DB_HOST'),
        'database'  => getenv('DB_DATABASE'),
        'username'  => getenv('DB_USERNAME'),
        'password'  => getenv('DB_PASSWORD'),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'options'   => [
            PDO::ATTR_TIMEOUT => 5,
        ],
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

    $logger->info('Starting migrations');

    foreach ($migrations as $migrationClass) {
        $migrationClass::up();
        $logger->info("Migration executed: {$migrationClass}");
        echo "Migration {$migrationClass} exécutée.\n";
    }

    $logger->info('Migrations completed');
    echo "Migration terminée !\n";
} catch (PDOException $e) {
    $logger->error("Failed to create database: " . $e->getMessage());
    fwrite(STDERR, "Database error: " . $e->getMessage() . "\n");
    exit(1);
} catch (RuntimeException $e) {
    $logger->error($e->getMessage());
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
} catch (Exception $e) {
    $logger->error("Migration failed: " . $e->getMessage());
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}