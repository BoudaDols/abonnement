<?php

   use Illuminate\Database\Capsule\Manager as Capsule;

   return function () {
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

      // rendre Eloquent dispo globalement
      $capsule->setAsGlobal();
      $capsule->bootEloquent();
   };