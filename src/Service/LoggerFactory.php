<?php
   namespace App\Service;

   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;

   class LoggerFactory {
      public function createLogger(string $name): Logger {
         $log = new Logger($name);
         $log->pushHandler(new StreamHandler(__DIR__ . '/../../var/app.log', Logger::DEBUG));
         return $log;
      }
   }