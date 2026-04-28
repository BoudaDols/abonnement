<?php

   use Illuminate\Database\Capsule\Manager as Capsule;

   return function () {
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

      // rendre Eloquent dispo globalement
      $capsule->setAsGlobal();
      $capsule->bootEloquent();
   };