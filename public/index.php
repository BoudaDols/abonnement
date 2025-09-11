<?php
   require __DIR__ . '/../vendor/autoload.php';

   use App\App;

   $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
   $dotenv->load();

   $app = new App();
   echo $app->run();