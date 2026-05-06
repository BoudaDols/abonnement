<?php

try {
    if ($argc < 2) {
        throw new InvalidArgumentException("Usage: php bin/make-model.php ModelName\n");
    }

    $modelName = $argv[1];
    $tableName = strtolower($modelName) . 's';
    $modelPath = __DIR__ . "/../src/Model/{$modelName}.php";
    $migrationDir = __DIR__ . '/../src/Migration';
    $existingMigrations = glob($migrationDir . '/*.php');
    $nextPrefix = str_pad(count($existingMigrations) + 1, 3, '0', STR_PAD_LEFT);
    $migrationPath = "{$migrationDir}/{$nextPrefix}_Create{$modelName}sTable.php";

    if (file_exists($modelPath)) {
        throw new RuntimeException("Model {$modelName} already exists!\n");
    }

    // Model template
    $modelTemplate = "<?php

namespace App\\Model;

class {$modelName} extends BaseModel
{
    protected \$fillable = [];
}";

    // Migration template
    $migrationTemplate = "<?php

namespace App\\Migration;

use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Database\\Capsule\\Manager as Capsule;

class Create{$modelName}sTable
{
    public static function up(): void
    {
        if (!Capsule::schema()->hasTable('{$tableName}')) {
            Capsule::schema()->create('{$tableName}', function (Blueprint \$table) {
                \$table->id();
                \$table->timestamps();
            });
        }
    }
    
    public static function down(): void
    {
        Capsule::schema()->dropIfExists('{$tableName}');
    }
}";

    file_put_contents($modelPath, $modelTemplate);
    file_put_contents($migrationPath, $migrationTemplate);

    echo "Model {$modelName} created at src/Model/{$modelName}.php\n";
    echo "Migration Create{$modelName}sTable created at src/Migration/Create{$modelName}sTable.php\n";
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
} catch (RuntimeException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
}