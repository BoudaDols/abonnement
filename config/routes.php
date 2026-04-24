<?php

   use App\Controller\HomeController;
   use App\Controller\PlanController;
   use App\Controller\SubscriptionController;
   use App\Controller\PaymentController;
   use App\Controller\WebhookController;
   use App\Controller\DocsController;

   return function (\FastRoute\RouteCollector $route) {
      $route->addRoute('GET', '/', [HomeController::class, 'index']);
      $route->addRoute('GET', '/api/docs', [DocsController::class, 'index']);
      $route->addRoute('GET', '/openapi.yaml', [DocsController::class, 'spec']);

      $route->addRoute('GET',    '/api/plans',              [PlanController::class, 'index']);
      $route->addRoute('GET',    '/api/plans/{id}',         [PlanController::class, 'show']);

      $route->addRoute('POST',   '/api/subscriptions',      [SubscriptionController::class, 'create']);
      $route->addRoute('GET',    '/api/subscriptions/{id}', [SubscriptionController::class, 'show']);
      $route->addRoute('DELETE', '/api/subscriptions/{id}', [SubscriptionController::class, 'delete']);

      $route->addRoute('GET',    '/api/payments',           [PaymentController::class, 'index']);
      $route->addRoute('POST',   '/api/payments',           [PaymentController::class, 'create']);

      $route->addRoute('POST',   '/api/webhooks/stripe',    [WebhookController::class, 'stripe']);
      $route->addRoute('POST',   '/api/webhooks/paypal',    [WebhookController::class, 'paypal']);
   };