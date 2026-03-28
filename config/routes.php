<?php

   use App\Controller\HomeController;
   use App\Controller\PlanController;
   use App\Controller\SubscriptionController;
   use App\Controller\PaymentController;

   return function (\FastRoute\RouteCollector $route) {
      $route->addRoute('GET', '/', [HomeController::class, 'index']);

      $route->addRoute('GET',    '/api/plans',              [PlanController::class, 'index']);
      $route->addRoute('GET',    '/api/plans/{id}',         [PlanController::class, 'show']);

      $route->addRoute('POST',   '/api/subscriptions',      [SubscriptionController::class, 'create']);
      $route->addRoute('GET',    '/api/subscriptions/{id}', [SubscriptionController::class, 'show']);
      $route->addRoute('DELETE', '/api/subscriptions/{id}', [SubscriptionController::class, 'delete']);

      $route->addRoute('GET',    '/api/payments',           [PaymentController::class, 'index']);
      $route->addRoute('POST',   '/api/payments',           [PaymentController::class, 'create']);
   };