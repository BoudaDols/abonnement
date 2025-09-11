<?php

if ($argc < 2) {
    echo "Usage: php bin/make-controller.php ControllerName\n";
    exit(1);
}

$controllerName = $argv[1];
$controllerPath = __DIR__ . "/../src/Controller/{$controllerName}.php";

if (file_exists($controllerPath)) {
    echo "Controller {$controllerName} already exists!\n";
    exit(1);
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