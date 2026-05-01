<?php

   namespace App\Service;

   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;
   use Monolog\Formatter\JsonFormatter;

class LoggerFactory
{
    public function createLogger(string $name): Logger
    {
         $log = new Logger($name);

         // File handler
         $log->pushHandler(new StreamHandler(__DIR__ . '/../../var/app.log', Logger::DEBUG));

         // Stdout handler for cloud logging (JSON format)
         $stdoutHandler = new StreamHandler('php://stdout', Logger::DEBUG);
         $stdoutHandler->setFormatter(new JsonFormatter());
         $log->pushHandler($stdoutHandler);

         return $log;
    }
}
