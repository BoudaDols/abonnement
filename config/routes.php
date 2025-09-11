<?php

   use App\Controller\HomeController;

   return function (\FastRoute\RouteCollector $route) {
      $route->addRoute('GET', '/', [HomeController::class, 'index']);
      $route->addRoute('GET', '/hello/{name}', [HomeController::class, 'hello']);
   };