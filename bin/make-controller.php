<?php

try {
    if ($argc < 2) {
        throw new InvalidArgumentException("Usage: php bin/make-controller.php ControllerName\n");
    }

    $controllerName = $argv[1];
    $controllerPath = __DIR__ . "/../src/Controller/{$controllerName}.php";

    if (file_exists($controllerPath)) {
        throw new RuntimeException("Controller {$controllerName} already exists!\n");
    }

    $template = "<?php

namespace App\\Controller;

class {$controllerName}
{
    public function index()
    {
        // List all
    }
    
    public function show(\$id)
    {
        // Show one
    }
    
    public function create()
    {
        // Create new
    }
    
    public function update(\$id)
    {
        // Update existing
    }
    
    public function delete(\$id)
    {
        // Delete
    }
}";

    file_put_contents($controllerPath, $template);
    echo "Controller {$controllerName} created at src/Controller/{$controllerName}.php\n";
} catch (InvalidArgumentException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
} catch (RuntimeException $e) {
    fwrite(STDERR, $e->getMessage());
    exit(1);
}