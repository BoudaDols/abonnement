<?php
require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Seeder\PlanSeeder;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

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
        PDO::ATTR_TIMEOUT => 5, // Prevent infinite hangs
    ],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

PlanSeeder::run();

echo "Seeding completed!\n";