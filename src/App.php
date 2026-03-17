<?php
   namespace App;

   use Dotenv\Dotenv;
   use FastRoute\RouteCollector;
   use function FastRoute\simpleDispatcher;
   use App\Service\LoggerFactory;

   class App {
      private $logger;

      public function __construct() {
         // Charger .env
         $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
         $dotenv->load();

         // Créer logger
         $this->logger = (new LoggerFactory())->createLogger('app');

         // Config DB
         $db = require __DIR__ . '/../config/database.php';
         $db();
      }

      public function run() {
         header('Content-Type: application/json');

         // Dispatcher FastRoute
         $dispatcher = simpleDispatcher(require __DIR__ . '/../config/routes.php');

         $httpMethod = $_SERVER['REQUEST_METHOD'];
         $uri = $_SERVER['REQUEST_URI'];

         if (false !== $pos = strpos($uri, '?')) {
               $uri = substr($uri, 0, $pos);
         }
         $uri = rawurldecode($uri);

         $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

         switch ($routeInfo[0]) {
               case \FastRoute\Dispatcher::NOT_FOUND:
                  http_response_code(404);
                  echo json_encode(['error' => 'Not Found']);
                  break;
               case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                  http_response_code(405);
                  echo json_encode(['error' => 'Method Not Allowed']);
                  break;
               case \FastRoute\Dispatcher::FOUND:
                  $handler = $routeInfo[1];
                  $vars = $routeInfo[2];
                  [$class, $method] = $handler;
                  $controller = new $class($this->logger);
                  $result = call_user_func_array([$controller, $method], $vars);
                  $this->logger->info("{$httpMethod} {$uri} -> {$class}::{$method}");
                  echo is_string($result) ? $result : json_encode($result);
                  break;
         }
      }
   }